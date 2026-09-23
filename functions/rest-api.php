<?php

if (!defined('ABSPATH')) {
    exit;
}

function avtodealer_rest_can_edit()
{
    return current_user_can('edit_theme_options') || current_user_can('manage_options');
}

function avtodealer_rest_json(WP_REST_Request $request)
{
    $params = $request->get_json_params();
    if (!is_array($params)) {
        $params = $request->get_params();
    }

    return is_array($params) ? $params : [];
}

function avtodealer_rest_wrap($data, $slug, $lang = null)
{
    $lang = $lang ?: ($data['lang'] ?? avtodealer_current_lang());
    $data['lang'] = $lang;
    $data['i18n'] = avtodealer_lang_meta($lang);
    $data['crud'] = [
        'url'     => rest_url('avtodealer/v1/' . $slug) . '?lang=' . rawurlencode($lang),
        'methods' => ['GET', 'POST', 'PUT', 'DELETE'],
    ];

    return $data;
}

function avtodealer_rest_resources()
{
    return [
        'header'    => ['get' => 'avtodealer_get_header', 'save' => 'avtodealer_save_header', 'delete' => 'avtodealer_delete_header'],
        'hero'           => ['get' => 'avtodealer_get_hero', 'save' => 'avtodealer_save_hero', 'delete' => 'avtodealer_delete_hero'],
        'hero-tank300'   => ['get' => 'avtodealer_get_hero_tank300', 'save' => 'avtodealer_save_hero_tank300', 'delete' => 'avtodealer_delete_hero_tank300'],
        'hero-tank500'   => ['get' => 'avtodealer_get_hero_tank500', 'save' => 'avtodealer_save_hero_tank500', 'delete' => 'avtodealer_delete_hero_tank500'],
        'catalog'        => ['get' => 'avtodealer_get_catalog', 'save' => 'avtodealer_save_catalog', 'delete' => 'avtodealer_delete_catalog'],
        'models'         => ['get' => 'avtodealer_get_models', 'save' => 'avtodealer_save_models', 'delete' => 'avtodealer_delete_models'],
        'configs'        => ['get' => 'avtodealer_get_configs', 'save' => 'avtodealer_save_configs', 'delete' => 'avtodealer_delete_configs'],

        'tradein'   => ['get' => 'avtodealer_get_tradein', 'save' => 'avtodealer_save_tradein', 'delete' => 'avtodealer_delete_tradein'],
        'credit'    => ['get' => 'avtodealer_get_credit', 'save' => 'avtodealer_save_credit', 'delete' => 'avtodealer_delete_credit'],
        'corporate' => ['get' => 'avtodealer_get_corporate', 'save' => 'avtodealer_save_corporate', 'delete' => 'avtodealer_delete_corporate'],
        'contact'   => ['get' => 'avtodealer_get_contact', 'save' => 'avtodealer_save_contact', 'delete' => 'avtodealer_delete_contact'],
        'footer'    => ['get' => 'avtodealer_get_footer', 'save' => 'avtodealer_save_footer', 'delete' => 'avtodealer_delete_footer'],
        'modal'     => ['get' => 'avtodealer_get_modal', 'save' => 'avtodealer_save_modal', 'delete' => 'avtodealer_delete_modal'],
    ];
}

function avtodealer_rest_slug(WP_REST_Request $request)
{
    $route = (string) $request->get_route();
    $parts = array_values(array_filter(explode('/', $route)));
    $slug = end($parts);
    $map = avtodealer_rest_resources();

    return isset($map[$slug]) ? $slug : '';
}

function avtodealer_rest_resource_get(WP_REST_Request $request)
{
    $slug = avtodealer_rest_slug($request);
    $map = avtodealer_rest_resources();
    if ($slug === '' || !function_exists($map[$slug]['get'])) {
        return new WP_Error('avto_missing', 'Resource not found', ['status' => 404]);
    }
    $lang = avtodealer_current_lang($request);

    return rest_ensure_response(avtodealer_rest_wrap(call_user_func($map[$slug]['get'], $lang), $slug, $lang));
}

function avtodealer_rest_resource_create(WP_REST_Request $request)
{
    $slug = avtodealer_rest_slug($request);
    $map = avtodealer_rest_resources();
    if ($slug === '' || !function_exists($map[$slug]['save'])) {
        return new WP_Error('avto_missing', 'Resource not found', ['status' => 404]);
    }
    $lang = avtodealer_current_lang($request);
    call_user_func($map[$slug]['save'], avtodealer_rest_json($request), true, $lang);
    $request->set_param('_avto_slug', $slug);

    return avtodealer_rest_resource_get($request);
}

function avtodealer_rest_resource_update(WP_REST_Request $request)
{
    $slug = avtodealer_rest_slug($request);
    $map = avtodealer_rest_resources();
    if ($slug === '' || !function_exists($map[$slug]['save'])) {
        return new WP_Error('avto_missing', 'Resource not found', ['status' => 404]);
    }
    $lang = avtodealer_current_lang($request);
    call_user_func($map[$slug]['save'], avtodealer_rest_json($request), false, $lang);

    return avtodealer_rest_resource_get($request);
}

function avtodealer_rest_resource_delete(WP_REST_Request $request)
{
    $slug = avtodealer_rest_slug($request);
    $map = avtodealer_rest_resources();
    if ($slug === '' || !function_exists($map[$slug]['delete'])) {
        return new WP_Error('avto_missing', 'Resource not found', ['status' => 404]);
    }
    $lang = avtodealer_current_lang($request);
    call_user_func($map[$slug]['delete'], $lang);

    return avtodealer_rest_resource_get($request);
}

function avtodealer_rest_index()
{
    $base = rest_url('avtodealer/v1');
    $resources = [];
    foreach (array_keys(avtodealer_rest_resources()) as $slug) {
        $resources[$slug] = [
            'url'     => $base . '/' . $slug,
            'methods' => ['GET', 'POST', 'PUT', 'DELETE'],
            'auth'    => [
                'GET'    => false,
                'POST'   => true,
                'PUT'    => true,
                'DELETE' => true,
            ],
            'query'   => ['lang' => 'ru|uz|en'],
        ];
    }

    return rest_ensure_response([
        'namespace' => 'avtodealer/v1',
        'methods'   => ['GET', 'POST', 'PUT', 'DELETE'],
        'i18n'      => avtodealer_lang_meta(),
        'resources' => $resources,
        'leads'     => [
            'url'     => $base . '/leads',
            'methods' => ['GET', 'POST'],
            'auth'    => ['GET' => true, 'POST' => false],
            'note'    => 'POST — public forma; GET — faqat admin',
        ],
        'hint'      => 'Schema: /avtodealer/v1/schema · Resource: /avtodealer/v1/{slug}?lang=ru · Query: ?rest_route=/avtodealer/v1/{slug}&lang=ru',
    ]);
}

function avtodealer_rest_register_resource($slug)
{
    register_rest_route('avtodealer/v1', '/' . $slug, [
        [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'avtodealer_rest_resource_get',
            'permission_callback' => '__return_true',
            'args'                => [
                'lang' => [
                    'type'     => 'string',
                    'required' => false,
                ],
            ],
        ],
        [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'avtodealer_rest_resource_create',
            'permission_callback' => 'avtodealer_rest_can_edit',
        ],
        [
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => 'avtodealer_rest_resource_update',
            'permission_callback' => 'avtodealer_rest_can_edit',
        ],
        [
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => 'avtodealer_rest_resource_delete',
            'permission_callback' => 'avtodealer_rest_can_edit',
        ],
    ]);
}

function avtodealer_register_rest()
{
    register_rest_route('avtodealer/v1', '/schema', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'avtodealer_rest_index',
        'permission_callback' => '__return_true',
    ]);

    // Eski yo‘l: ba’zi klientlar / ni kutadi — WP discovery bilan to‘qnashadi, shuning uchun ham schema.
    register_rest_route('avtodealer/v1', '/index', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'avtodealer_rest_index',
        'permission_callback' => '__return_true',
    ]);

    foreach (array_keys(avtodealer_rest_resources()) as $slug) {
        avtodealer_rest_register_resource($slug);
    }

    register_rest_route('avtodealer/v1', '/leads', [
        [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'avtodealer_rest_lead_create',
            'permission_callback' => '__return_true',
        ],
        [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'avtodealer_rest_leads_list',
            'permission_callback' => 'avtodealer_rest_can_edit',
        ],
    ]);
}
add_action('rest_api_init', 'avtodealer_register_rest');
