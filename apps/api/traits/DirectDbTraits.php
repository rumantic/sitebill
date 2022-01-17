<?php
namespace api\traits;

use Illuminate\Database\Capsule\Manager as Capsule;

trait DirectDbTraits {

    function direct_toggle ( $table_name, $primary_key, $primary_key_value, $toggled_column ) {
        // Select previous value
        $previous = Capsule::table($table_name)
            ->select(
                $toggled_column
                )
            ->where($primary_key, '=', $primary_key_value)
            ->first();
        if ( $previous ) {
            $new  = Capsule::table($table_name)
                ->where($primary_key, '=', $primary_key_value)
                ->update(
                    [$toggled_column => !$previous->$toggled_column]
                );
            return true;
        }
        return false;
    }
}
