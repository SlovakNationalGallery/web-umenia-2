<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use App\Authority;
use Fadion\Bouncy\Facades\Elastic;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;

use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class AuthorController extends ElasticController
{

    public function getIndex()
    {
        $per_page = 18;
        $page = \Input::get('page', 1);
        // $page = Paginator::resolveCurrentPage();
        // dd($page);
        $offset = ($page * $per_page) - $per_page;

        $search = Input::get('search', null);
        $input = Input::all();


        if (Input::has('sort_by') && array_key_exists(Input::get('sort_by'), Authority::$sortable)) {
            $sort_by = Input::get('sort_by');
        } else {
            $sort_by = "items_with_images_count";
        }

        $sort_order = ($sort_by == 'name') ? 'asc' : 'desc';

        $params = array();
        $params["from"] = $offset;
        $params["size"] = $per_page;

        if ($sort_by=='random') {
            $random = json_decode('
				{"_script": {
				    "script": "Math.random() * 200000",
				    "type": "number",
				    "params": {},
				    "order": "asc"
				 }}', true);
            $params["sort"][] = $random;
        } else {
            $params["sort"][] = "_score";
            // $params["sort"][] = ["created_at"=>["order"=>"desc"]];
            $params["sort"][] = ["$sort_by"=>["order"=>$sort_order]];
            $params["sort"][] = ["items_count"=>["order"=>"desc"]];
            $params["sort"][] = ["has_image"=>["order"=>"desc"]];
        }

        if (!empty($input)) {
            if (Input::has('search')) {
                $search = Input::get('search', '');
                $search = str_to_alphanumeric($search);

                $should_match = [
                    'name' => $search,
                    'alternative_name' => $search,
                    'name.folded' => $search,
                    'biography' =>  $search,
                    'biography.stemmed' => [
                        'query' => $search,
                        'boost' => 0.9,
                    ],
                    'biography.stemmed' => [
                        'query' => $search,
                        'analyzer' => $this->elastic_translatable->getAnalyzerNameForSynonyms(),
                        'boost' => 0.5,
                    ],
                    'place.folded' => [
                        'query' => $search,
                        'boost' => 0.5,
                    ],
                ];

                $should = [];
                foreach ($should_match as $key => $match) {
                    $should[] = ['match' => [$key => $match]];
                }

                $params['query']['bool']['should'] = $should;
                $params['query']['bool']['minimum_should_match'] = 1;
            }

            foreach ($input as $filter => $value) {
                if (in_array($filter, Authority::$filterable) && !empty($value)) {
                    $params["query"]["filtered"]["filter"]["bool"]["must"][]["term"][$filter] = $value;
                }
            }
            if (!empty($input['year-range']) &&
                $input['year-range']!=Authority::sliderMin().','.Authority::sliderMax() //nezmenena hodnota
            ) {
                $range = explode(',', $input['year-range']);
                $params["query"]["filtered"]["filter"]["bool"]["should"][]["range"]["death_year"]["gte"] = (string)$range[0];
                $params["query"]["filtered"]["filter"]["bool"]["should"][]["missing"]["field"] = "death_year";
                $params["query"]["filtered"]["filter"]["bool"]["must"][]["range"]["birth_year"]["lte"] = (string)$range[1];
            }
            if (!empty($input['first-letter'])) {
                $params["query"]["filtered"]["filter"]["bool"]["must"][]["prefix"]["name"] = $input['first-letter'];
            }
        }

        $authors = Authority::search($params); //->paginate($per_page);

        $path   = '/' . \Request::path();
        // dd($path);
        $page   = Paginator::resolveCurrentPage() ?: 1;
        $paginator = new LengthAwarePaginator($authors->all(), $authors->total(), $per_page, $page, ['path' => $path]);
        // dd($paginator);


        // $authors = Authority::listValues('author', $params);
        $roles = Authority::listValues('role', $params);
        $nationalities = Authority::listValues('nationality', $params);
        $places = Authority::listValues('place', $params);
        // dd($roles);
        return view('autori', array(
            'authors'=>$authors,
            'search'=>$search,
            'sort_by'=>$sort_by,
            'roles'=>$roles,
            'nationalities'=>$nationalities,
            'places'=>$places,
            'input'=>$input,
            'paginator'=>$paginator,
            ));
    }

    public function getSuggestions()
    {
        $q = (Input::has('search')) ? str_to_alphanumeric(Input::get('search')) : 'null';

        $result = Elastic::search([
                'index' => Config::get('bouncy.index'),
                'type' => Authority::ES_TYPE,
                'body'  => array(
                    'query' => array(
                        'multi_match' => array(
                            'query'     => $q,
                            'type'      => 'cross_fields',
                            // 'fuzziness' =>  2,
                            // 'slop'		=>  2,
                            'fields'    => array("name.suggest", "alternative_name.suggest"),
                            'operator'  => 'and'
                        ),
                    ),
                    'size' => '10',
                    'sort' => [
                        'items_count' => ['order' => 'desc'],
                        'has_image' => ['order' => 'desc'],
                    ]
                ),
            ]);

        $data = array();
        $data['results'] = array();
        $data['count'] = 0;

        // $data['items'] = array();
        foreach ($result['hits']['hits'] as $key => $hit) {

            $name = preg_replace('/^([^,]*),\s*(.*)$/', '$2 $1', $hit['_source']['name']);

            $data['count']++;
            $params = array(
                'id' => $hit['_id'],
                'name' => $name,
                'birth_year' => $hit['_source']['birth_year'],
                'death_year' => $hit['_source']['death_year'],
                'image' => Authority::getImagePathForId($hit['_id'], $hit['_source']['has_image'], $hit['_source']['sex'], false, 70)
            );
            $data['results'][] = array_merge($params) ;
        }

        return Response::json($data);
    }

    public function getDetail($id)
    {
        $author = Authority::find($id);
        if (empty($author)) {
            App::abort(404);
        }

        $author->timestamps = false;
        $author->view_count += 1;
        $author->save();
        return view('autor', array('author'=>$author));

    }
}
