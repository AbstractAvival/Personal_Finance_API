<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ResourceCollectionBase extends ResourceCollection
{
    public function toArray( Request $request ): array
    {
        return parent::toArray( $request );
    }


    public function paginationInformation( $request, $paginated, $default )
    {
        unset( $default[ 'links' ] );
        unset( $default[ 'meta' ][ 'path' ] );
        unset( $default[ 'meta' ][ 'links' ] );

        return $default;
    }
}
