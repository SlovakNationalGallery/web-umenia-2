<?php

namespace App\Http\Controllers;

use Zizaco\Entrust\EntrustFacade;
use App\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Intervention\Image\ImageManagerStatic;
use Illuminate\Support\Facades\Response;

class CollectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        if (\Entrust::hasRole('admin')) {
            $collections = Collection::orderBy('created_at', 'desc')->with(['user'])->paginate(20);
        } else {
            $collections = Collection::where('user_id', '=', Auth::user()->id)->orderBy('published_at', 'desc')->with(['user'])->paginate(20);
        }

        return view('collections.index')->with('collections', $collections);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('collections.form');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $input = Input::all();

        $rules = Collection::$rules;
        $v = Validator::make($input, $rules);

        if ($v->passes()) {
            $collection = new Collection();

            // store translatable attributes
            foreach (\Config::get('translatable.locales') as $i => $locale) {
                if (hasTranslationValue($locale, $collection->translatedAttributes)){
                    foreach ($collection->translatedAttributes as $attribute) {
                        $collection->translateOrNew($locale)->$attribute = Input::get($locale . '.' . $attribute);
                    }
                }
            }

            $collection->published_at = Input::get('published_at');

            if (Input::has('title_color')) {
                $collection->title_color = Input::get('title_color');
            }
            if (Input::has('title_shadow')) {
                $collection->title_shadow = Input::get('title_shadow');
            }
            $collection->order = Collection::max('order') + 1;
            if (Input::has('user_id') && \Entrust::hasRole('admin')) {
                $collection->user_id = Input::get('user_id');
            } else {
                $collection->user_id = Auth::user()->id;
            }

            $collection->save();

            if (Input::hasFile('main_image')) {
                $this->uploadMainImage($collection);
            }

            return Redirect::route('collection.index');
        }

        return Redirect::back()->withInput()->withErrors($v);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $collection = Collection::find($id);

        return view('collections.show')->with('collection', $collection);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $collection = Collection::find($id);

        if (is_null($collection)) {
            return Redirect::route('collection.index');
        }

        return view('collections.form')->with('collection', $collection);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function update($id)
    {
        $v = Validator::make(Input::all(), Collection::$rules);

        if ($v->passes()) {
            $input = array_except(Input::all(), array('_method'));

            $collection = Collection::find($id);

            foreach (\Config::get('translatable.locales') as $i => $locale) {
                if (hasTranslationValue($locale, $collection->translatedAttributes)){
                    foreach ($collection->translatedAttributes as $attribute) {
                        $collection->translateOrNew($locale)->$attribute = Input::get($locale . '.' . $attribute);
                    }
                }
            }

            $collection->published_at = Input::get('published_at', null);

            if (Input::has('user_id') && \Entrust::hasRole('admin')) {
                $collection->user_id = Input::get('user_id');
            }

            if (Input::has('title_color')) {
                $collection->title_color = Input::get('title_color');
            }
            if (Input::has('title_shadow')) {
                $collection->title_shadow = Input::get('title_shadow');
            }
            $collection->order = Input::get('order');
            $collection->save();

            if (Input::hasFile('main_image')) {
                $this->uploadMainImage($collection);
            }

            Session::flash('message', 'Kolekcia <code>' . $collection->name . '</code> bola upravená');

            return Redirect::route('collection.index');
        }

        return Redirect::back()->withErrors($v);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        Collection::find($id)->delete();

        return Redirect::route('collection.index')->with('message', 'Kolekcia bola zmazaná');
    }

    /**
     * Fill the collection with items.
     *
     * @param
     *
     * @return Response
     */
    public function fill()
    {
        if ($collection = Collection::find(Input::get('collection'))) {
            $items = Input::get('ids');
            if (!is_array($items)) {
                $items = explode(';', str_replace(' ', '', $items));
            }
            foreach ($items as $item_id) {
                if (!$collection->items->contains($item_id)) {
                    $collection->items()->attach($item_id);
                }
            }

            return Redirect::back()->withMessage('Do kolekcie ' . $collection->name . ' bolo pridaných ' . count($items) . ' diel');
        } else {
            return Redirect::back()->withMessage('Chyba: zvolená kolekcia nebola nájdená. ');
        }
    }

    public function detach($collection_id, $item_id)
    {
        $collection = Collection::find($collection_id);
        $collection->items()->detach($item_id);

        return Redirect::back()->withMessage('Z kolekcie <strong>' . $collection->name . '</strong> bolo odstrádené dielo <code>' . $item_id . '</code>');
    }

    private function uploadMainImage($collection)
    {
        $main_image = Input::file('main_image');
        $collection->main_image = $collection->uploadHeaderImage($main_image);
        $collection->save();
    }

    public function sort()
    {
        $entity = \Input::get('entity');
        $model_name = studly_case($entity);
        // $model  = $model_name::find(\Input::get('id'));
        $collection = Collection::find(\Input::get('id'));

        $ids = (array) \Input::get('ids');
        $order = 0;
        $ordered_items = [];
        // $orders     = [];

        foreach ($ids as $id) {
            $ordered_items[$id] = ['order' => $order];
            ++$order;
        }

        $collection->items()->sync($ordered_items);

        $response = [
            'result' => 'success',
            'message' => 'poradie zmenene',
            'entity' => $entity,
            // 'orders'  => $orders,
        ];

        return response()->json($response);
    }
}
