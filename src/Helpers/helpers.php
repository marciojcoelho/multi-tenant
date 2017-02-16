<?php

if ( ! function_exists('tenant')) {
    /**
     * Get the current tenant.
     * Retrieves the given key value if the parameter is present.
     *
     * @param  int $key
     * @return mixed
     */
    function tenant($key = null)
    {
        static $tenant;

        if(is_null($tenant)) {
            $tenant = app('Tenant');
        }

        if ($key) {
          if (isset($tenant[$key])) {
              return $tenant[$key];
          } else {
            return null;
          }
        }

        return $tenant;
    }

}

if ( ! function_exists('tenant_path')) {
    /**
     * Get the path to the given tenant directory.
     *
     * @param  int|array $tenant
     * @param  string $affix
     * @return string
     */
    function tenant_path($tenant, $affix = '')
    {
        $path = config('tenant.path') . '/';

        is_array($tenant) && array_key_exists('id', $tenant) ?
            $path .= $tenant['id'] :
            $path .= $tenant;

        return $path . $affix;
    }
}

if ( ! function_exists('tasset')) {
    /**
     * Get the route to retrieve the given tenant asset.
     *
     * Uses a fallback to load the asset from the default
     * directory in case the tenant asset doesn't exist.
     *
     * @param  string $path
     * @param  bool $secure
     * @return string
     */
    function tasset($path, $secure = null)
    {
        $tenant = tenant();
        $asset = tenant_path($tenant, '/assets/' . $path);

        if ($tenant && file_exists($asset)) {
            return route('asset', [$path]);
        }

        return asset($path, $secure);
    }
}

if ( ! function_exists('ttrans')) {

    function ttrans($id = null, $parameters = [], $domain = 'messages', $locale = null)
    {
        if (array_key_exists('translation.domain', app()->getInstance()->getBindings())) {

            if (is_null($id)) {
                return app('translation.domain');
            }

            return app('translation.domain')->trans($id, $parameters, $domain, $locale);
        }

        if (is_null($id)) {
            return app('translator');
        }

        return app('translator')->trans($id, $parameters, $domain, $locale);
    }
}

if ( ! function_exists('ttrans_choice')) {
    function ttrans_choice($id, $number, array $parameters = [], $domain = 'messages', $locale = null)
    {
        if (array_key_exists('translation.domain', app()->getInstance()->getBindings())) {
          return app('translation.domain')->transChoice($id, $number, $parameters, $domain, $locale);
        }

        return app('translator')->transChoice($id, $number, $parameters, $domain, $locale);
    }
}
