<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use \App\Entity\Pillow;
use \App\Entity\Mattress;
use \App\Entity\Textile;
use \App\Entity\Illumination;
use \App\Entity\BedAndFrame;
use \App\Entity\Furniture;

class Solr extends BaseService {

    private $config;
    private $client;
    private $query;

    private $filter_structure;

    private $query_string;
    private $option_query;

    private $facet_set;
    private $stats;
    private $result_set;

    public  $articles;
    public  $filters;
    public  $count;

    private $start = 0;
    private $limit = 21;
    private $facet_min_count = null;

    private $article_entities;

    /**
     * @required
     */
    public function init(Pillow $pillow, Mattress $mattress, Textile $textile, Illumination $illumination, BedAndFrame $bed_and_frame, Furniture $furniture)
    {
        $this->article_entities = [
            '6' => $furniture,
            '5' => $bed_and_frame,
            '4' => $illumination,
            '3' => $textile,
            '2' => $pillow,
            '1' => $mattress
        ];
    }

    // Filter structure to avoid duplicated code. Not static to allow names to be translated
    private function loadFilterStructure($category_id)
    {
        $filter_structure = [
            'format' => [
                'solr_field' => 'formats',
                'solr_facet_field' => 'formats',
                'name' => $this->translator->trans('Tamanho'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],
            'size' => [
                'solr_field' => 'sizes',
                'solr_facet_field' => 'sizes',
                'name' => $this->translator->trans('Medidas'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],
            'price' => [
                'solr_field' => 'translated_price_min',
                'solr_facet_field' => 'translated_price_min',
                'name' => $this->translator->trans('Preço'),
                'type' => 'range',
                'label_formatter_function' => ''
            ],
            'mattress_type' => [
                'solr_field' => 'translated_mattress_type_strings',
                'solr_facet_field' => 'translated_mattress_type_strings',
                'name' => $this->translator->trans('Núcleo'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],
            'level' => [
                'solr_field' => 'level',
                'solr_facet_field' => 'level',
                'name' => $this->translator->trans('Firmeza'),
                'type' => 'checkbox',
                'label_formatter_function' => '\App\Entity\Mattress::formatFirmness'
            ],
            'layers' => [
                'solr_field' => 'translated_layers',
                'solr_facet_field' => 'translated_layers',
                'name' => $this->translator->trans('Camadas'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],
            'characteristic' => [
                'solr_field' => 'translated_characteristics',
                'solr_facet_field' => 'translated_characteristics',
                'key' => 'characteristic',
                'name' => $this->translator->trans('Características'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],
            'brand' => [
                'solr_field' => 'brand_strings',
                'solr_facet_field' => 'brand_strings',
                'name' => $this->translator->trans('Marca'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],
            'deep' => [
                'solr_field' => 'deep',
                'solr_facet_field' => 'deep',
                'name' => $this->translator->trans('Altura'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],
            'color' => [
                'solr_field' => 'translated_color',
                'solr_facet_field' => 'translated_color',
                'name' => $this->translator->trans('Cor'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],
            /*[
                'solr_field' => 'translated_function',
                'solr_facet_field' => 'translated_function_strings',
                'key' => 'function',
                'name' => $this->translator->trans('Núcleo'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],*/
            'transport' => [
                'solr_field' => 'translated_transport',
                'solr_facet_field' => 'translated_transport_strings',
                'name' => $this->translator->trans('Modo de transporte'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],
            /*[
                'solr_field' => 'weight',
                'solr_facet_field' => 'weight',
                'key' => 'weight',
                'name' => $this->translator->trans('Peso'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],*/
            'stamping' => [
                'solr_field' => 'stamping',
                'solr_facet_field' => 'stamping',
                'name' => $this->translator->trans('Estampado'),
                'type' => 'checkbox',
                'label_formatter_function' => '\App\Entity\Textile::formatStamping'
            ],
            'shape' => [
                'solr_field' => 'translated_shape_strings',
                'solr_facet_field' => 'translated_shape_strings',
                'name' => $this->translator->trans('Forma'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],
            'finishing' => [
                'solr_field' => 'translated_finishing_strings',
                'solr_facet_field' => 'translated_finishing_strings',
                'name' => $this->translator->trans('Acabamento'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],
            'material' => [
                'solr_field' => 'translated_material_strings',
                'solr_facet_field' => 'translated_material_strings',
                'name' => $this->translator->trans('Materiais'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],
            'textile_type' => [
                'solr_field' => 'translated_textile_type_strings',
                'solr_facet_field' => 'translated_textile_type_strings',
                'name' => $this->translator->trans('Tipo'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],
            'illumination_type' => [
                'solr_field' => 'translated_illumination_type_strings',
                'solr_facet_field' => 'translated_illumination_type_strings',
                'name' => $this->translator->trans('Tipo'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],
            'style' => [
                'solr_field' => 'translated_style',
                'solr_facet_field' => 'translated_style',
                'name' => $this->translator->trans('Estilo'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],
            'lamp_power' => [
                'solr_field' => 'translated_lamp_power',
                'solr_facet_field' => 'translated_lamp_power',
                'name' => $this->translator->trans('Potência lâmpadas'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],
            'adjustable_light_intensity' => [
                'solr_field' => 'adjustable_light_intensity',
                'solr_facet_field' => 'adjustable_light_intensity',
                'name' => $this->translator->trans('Intensidade de luz regulável'),
                'type' => 'checkbox',
                'label_formatter_function' => '\App\Entity\Illumination::formatYesNo'
            ],
            'light_switch' => [
                'solr_field' => 'light_switch',
                'solr_facet_field' => 'light_switch',
                'name' => $this->translator->trans('Interruptor'),
                'type' => 'checkbox',
                'label_formatter_function' => '\App\Entity\Illumination::formatYesNo'
            ],
            'lamps_included' => [
                'solr_field' => 'lamps_included',
                'solr_facet_field' => 'lamps_included',
                'name' => $this->translator->trans('Inclui lâmpadas'),
                'type' => 'checkbox',
                'label_formatter_function' => '\App\Entity\Illumination::formatYesNo'
            ],
            'bushing' => [
                'solr_field' => 'translated_bushing',
                'solr_facet_field' => 'translated_bushing',
                'name' => $this->translator->trans('Casquilho'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],
            'bed_and_frame_type' => [
                'solr_field' => 'translated_bed_and_frame_type_strings',
                'solr_facet_field' => 'translated_bed_and_frame_type_strings',
                'name' => $this->translator->trans('Tipo'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],
            'furniture_type' => [
                'solr_field' => 'translated_furniture_type_strings',
                'solr_facet_field' => 'translated_furniture_type_strings',
                'name' => $this->translator->trans('Tipo'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],
            'pillow_type' => [
                'solr_field' => 'translated_pillow_type_strings',
                'solr_facet_field' => 'translated_pillow_type_strings',
                'name' => $this->translator->trans('Tipo'),
                'type' => 'checkbox',
                'label_formatter_function' => ''
            ],
        ];

        if (!$category_id) {
            $this->filter_structure = $filter_structure;
            return;
        }

        $entity = $this->article_entities[$category_id];

        foreach($entity::$attributes as $attribute) {
            $this->filter_structure[$attribute] = $filter_structure[$attribute];
        }
    }

    public function createBaseQuery($category_id)
    {
        $this->config = array(
            'endpoint' => array(
                'localhost' => array(
                    'host' => $this->params->get('solr.host'),
                    'port' => $this->params->get('solr.port'),
                    'path' => $this->params->get('solr.path'),
                    'core' => $this->params->get('solr.core'),
                )
            )
        );

        // create a client instance
        $this->client = new \Solarium\Client($this->config);

        // get a select query instance
        $this->query = $this->client->createSelect();

        $this->query->setQueryDefaultField('_text_');

        $this->loadFilterStructure($category_id);
    }

    public function setQuery($query_string)
    {
        $this->query_string = $query_string;
    }

    public function addSort($sort)
    {
        list($field, $order) = explode(':', $sort);
        $this->query->addSort($field, $order);
    }

    public function addPagination($page)
    {
        $this->query->setStart(($page-1) * $this->limit);
    }

    private function formatFilterQuery($filter_string, $filter_name)
    {
        $filter_array = explode(';', $filter_string);
        $filter_query = '';

        foreach($filter_array as $key => $filter) {
            $filter_query .= $filter_name . ':"' . $filter . '"';

            if ($key < (count($filter_array) -1)) {
                $filter_query .= ' OR ';
            }
        }

        return $filter_query;
    }

    public function addFilter($filter_string, $solr_field)
    {
        $this->query->addFilterQuery(['key' => $solr_field, 'query' => $this->formatFilterQuery($filter_string, $solr_field), 'tag' => $solr_field]);
    }

    // Not used
    public function addChildFilter($filter_string, $filter_name)
    {
        if (!$this->option_query) { $this->option_query = ''; }
        else { $this->option_query .= ' AND '; }
        $this->option_query .= $this->formatFilterQuery($filter_string, $filter_name);
    }

    public function addRangeFilter($filter_range, $filter_name)
    {
        $this->query->createFilterQuery($filter_name)->setQuery($filter_name .
            ':[' .
            (
                $filter_range[0] ?
                $filter_range[0] :
                '*'
            ) .
            ' TO ' .
            (
                $filter_range[1] ?
                $filter_range[1] :
                '*'
            ) .
            ']'
        )->addTag($filter_name);
    }

    public function addFacetsStats()
    {
        if (!$this->facet_set) {
            // create a facet field instance and set options using the API
            $this->facet_set = $this->query->getFacetSet();
        }
        if (!$this->stats) {
            $this->stats = $this->query->getStats();
        }

        foreach ($this->filter_structure as $key => $filter) {
            if ($filter['type'] == 'checkbox') {

                $facet_field = [
                    'key' => $filter['solr_field'],
                    'field' => $filter['solr_facet_field'],
                    'exclude' => $filter['solr_field'],
                ];

                if ($this->facet_min_count !== null) {
                    $facet_field['mincount'] = $this->facet_min_count;
                }
                $this->facet_set->createFacetField($facet_field);
            } elseif ($filter['type'] == 'range') {
                $this->stats->createField($filter['solr_field'])->addFacet($filter['solr_field']);
            }
        }

        // create a facet field instance and set options
        $facet = $this->facet_set->createFacetInterval('price_intervals');
        $facet->setField('translated_price_min');
        $facet->addExclude('translated_price_min');
        $facet->setSet(array('0-100' => '[0,100]', '100-200' => '[100,200]', '200-500' => '[200,500]', '>500' => '[500,*]'));
    }

    public function addFacetsCategories()
    {
        if (!$this->facet_set) {
            // create a facet field instance and set options using the API
            $this->facet_set = $this->query->getFacetSet();
        }
        $facet_field = [
            'key' => 'translated_category_title',
            'field' => 'translated_category_title'
        ];

        $facet = $this->facet_set->createFacetPivot('translated_category_title-category_id');
        $facet->addFields('translated_category_title,category_id');

        if ($this->facet_min_count !== null) {
            $facet->setMinCount($this->facet_min_count);
        }
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    public function setFacetMinCount($facet_min_count)
    {
        $this->facet_min_count = $facet_min_count;
    }

    public function execute()
    {
        $query_string = 'translated_active:1';
        if ($this->query_string) {
            $query_string .= ' AND (*' . str_replace(' ', '* AND *', $this->query_string) . '*)';
        }

        // apply settings using the API
        $this->query->setQuery($query_string);

        $option_query_final = $this->option_query ?
            'childFilter=\'(image_url:* OR (' . $this->option_query . '))\'' :
            '';

        $this->query->setFields(['*', '[child parentFilter=unique_id:* ' . $option_query_final . ' limit=100]']);

        $this->query->setRows($this->limit);

        // this executes the query and returns the result
        //$request = $this->client->createRequest($this->query);
        //var_dump($request->getUri());die('cenas');
        $this->result_set = $this->client->select($this->query);
        $this->count = $this->result_set->getNumFound();
        $this->articles = $this->result_set->getData()['response']['docs'];
    }

    public function formatFilters()
    {
        $this->filters = [];

        foreach($this->filter_structure as $key => $filter) {
            if ($filter['type'] === 'checkbox') {
                $facet_result = $this->result_set->getFacetSet()->getFacet($filter['solr_field']);
                $formatted_filter = [
                    'key' => $key,
                    'name' => $filter['name'],
                    'type' => $filter['type'],
                    'options' => []
                ];
                foreach($facet_result as $value => $count) {
                    if (!$value) { continue; }
                    $formatted_filter['options'][Utils::slugify($value)] = [
                        'value' => $value,
                        'label' => (string) ($filter['label_formatter_function'] ? $filter['label_formatter_function']($value, $this->translator) : $value),
                        'count' => $count
                    ];
                }
            } elseif ($filter['type'] === 'range') {
                $stat_result = $this->result_set->getStats()->getResult('translated_price_min');
                $formatted_filter = [
                    'key' => $key,
                    'name' => $filter['name'],
                    'type' => $filter['type'],
                    'min' => round($stat_result->getMin(), -1, PHP_ROUND_HALF_DOWN),
                    'max' => round($stat_result->getMax(), -1, PHP_ROUND_HALF_UP)
                ];

                $facet_result = $this->result_set->getFacetSet()->getFacet('price_intervals');
                foreach($facet_result as $value => $count) {
                    if (!$value) { continue; }
                    $formatted_filter['intervals'][$value] = $count;
                }
            }
            $this->filters[$key] = $formatted_filter;
        }
    }

    public function formatFiltersCategories()
    {
        $this->filters = [];

        $facet_result = $this->result_set->getFacetSet()->getFacet('translated_category_title-category_id');
        $formatted_filter = [
            'key' => 'category_title',
            'name' => 'category_title',
            'type' => 'title',
            'options' => []
        ];
        foreach($facet_result as $facet) {
            $id = '';
            if (isset($facet->getPivot()[0])) {
                $id = $facet->getPivot()[0]->getValue();
            }
            $formatted_filter['options'][Utils::slugify($facet->getValue())] = [
                'value' => $facet->getValue(),
                'label' => $facet->getValue(),
                'count' => $facet->getCount(),
                'id'    => $id
            ];
        }

        $this->filters['categories'] = $formatted_filter;
    }
}
?>
