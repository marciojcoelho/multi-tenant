<?php

namespace Mjc\MultiTenant\Models;

use Illuminate\Database\Eloquent\Model;

class TenantModel extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection;

    /**
     * Get the current connection name for the model.
     *
     * @return string
     */
    public function getConnectionName()
    {
        $tenant = tenant();

        if(is_null($tenant)) {
            return parent::getConnectionName();
        }

        return array_get($tenant, 'id');
    }
}
