<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class ResourceBaseClass extends JsonResource
{
    protected array $map = [];

    public function toArray( $request ): array
    {
        return array_merge(
            self::getPaginateMeta(),
            self::getData()
        );
    }

    private function getPaginateMeta(): array
    {
        if ( is_array( $this->resource ) && isset( $this->resource[ "data" ] ) ) {
            return [
                'meta' => [
                    "current_page" => $this->resource[ "current_page" ] ?? 1,
                    "last_page" => $this->resource[ "last_page" ] ?? 1,
                    "total" => $this->resource[ "total" ] ?? count( $this->resource[ "data" ] ),
                    "per_page" => $this->resource[ "per_page" ] ?? count( $this->resource[ "data" ] ),
                    "from" => $this->resource[ "from" ] ?? 1,
                    "to" => $this->resource[ "to" ] ?? count( $this->resource[ "data" ] ),
                ],
            ];
        }
        return [];
    }

    private function getData(): array
    {
        $response = $this->resource;

        if( is_array( $response ) && isset( $response[ "data" ] ) ) {
            // remove the index "data"
            $response = $this->mapData(
                $response[ "data" ],
                $this->map
            );
            $response = [
                "data" => $response,
            ];

            return $response;
        }
        if( is_array( $response ) ) {
            // map the array response to the map
            return $this->mapData( $response, $this->map );
        }
        return [];
    }

    private static function mapData( $item, $map ): array
    {
        $data = [];
        foreach( $item as $key => $value ) {
            if( isset( $map[ $key ] ) ) {
                $data[ $map[ $key ] ] = $value;
                continue;
            }
            if( is_array( $value ) ) {
                $data[] = self::mapData( $value, $map );
            }
        }
        return $data;
    }
}
