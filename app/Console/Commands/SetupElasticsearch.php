<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupElasticsearch extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'es:setup';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Create Elasticsearch index and types with proper mapping for specified locale.';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
      parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $BASE_PATH = "app/Console/Commands/SetupElasticsearch";

    $client = new \GuzzleHttp\Client(['http_errors' => false]);

    $host = 'localhost:9200';
    $hosts = config('elasticsearch.hosts');
    if (!empty($hosts)) {
      $host = reset($hosts);
      $this->comment('Your ES host is: ' . $host);
    }

    // get available locales based on directory names
    $available_locales = array_map('basename', glob($BASE_PATH ."/*", GLOB_ONLYDIR));

    // let user select from available locales
    $locale_options = array_merge(['all'], $available_locales);
    $selected_locale = $this->choice('Which locale(s) do you want to set up?', $locale_options, 0);

    if ($selected_locale == 'all') {
      foreach ($available_locales as $key => $locale) {
        $this->setup_for_locale($client, $host, $BASE_PATH, $locale);  
      }
    }
    else {
      $this->setup_for_locale($client, $host, $BASE_PATH, $selected_locale);
    }

    $this->info("\nDone 🎉");
  }

  public function setup_for_locale($client, $host, $BASE_PATH, $locale_str)
  {
    $this->comment("\nsetting up ES index for locale: $locale_str");
    $index_name = $this->get_index_name($client, $host, $locale_str);

    $json_params_create_index_str       = file_get_contents("$BASE_PATH/$locale_str/index.json");
    $json_params_create_items_str       = file_get_contents("$BASE_PATH/$locale_str/items.json");
    $json_params_create_authorities_str = file_get_contents("$BASE_PATH/$locale_str/authorities.json");

    $this->create_index($client, $host, $index_name, $json_params_create_index_str);
    $this->create_mapping($client, $host, $index_name, 'items'      , $json_params_create_items_str);
    $this->create_mapping($client, $host, $index_name, 'authorities', $json_params_create_authorities_str);
  }

  public function get_index_name($client, $host, $locale_str)
  {
    $default_index_name = 'webumenia_'.$locale_str;
    $index_name = $this->ask('What is the index name?', $default_index_name);

    $res = $client->head('http://'.$host.'/'.$index_name);

    if ($res->getStatusCode() == 200) {
        if ($this->confirm("❗ An index with that name already exists❗\n Do you want to delete the current index?\n [y|N]")) {
            $this->comment('Removing...');
            $res = $client->delete('http://'.$host.'/'.$index_name);
            echo $res->getBody() . "\n";
        }
    }

    return $index_name;
  }

  public function create_index($client, $host, $index_name, $index_params_str)
  {
    $this->comment('Creating index...');
    $res = $client->put('http://'.$host.'/'.$index_name, [
        'json' => json_decode($index_params_str, true),
    ]);
    echo $res->getBody() . "\n";

    if ($res->getStatusCode() == 200) {
        $this->info('Index ' . $index_name . ' was created');
    }
  }

  public function create_mapping($client, $host, $index_name, $mapping_name, $mapping_params_str)
  {
    $this->comment("Creating type $mapping_name...");
    $res = $client->put("http://$host/$index_name/_mapping/$mapping_name", [
        'json' => json_decode($mapping_params_str, true),
    ]);
    echo $res->getBody() . "\n";

    if ($res->getStatusCode() == 200) {
        $this->info("Type $mapping_name was created");
    }
  }
}