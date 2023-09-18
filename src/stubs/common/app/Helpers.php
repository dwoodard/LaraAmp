<?php

if (!function_exists('OnlyColumns')) {
  function OnlyColumns($model, $removeProperties = []): array
  {
    //set columns with \Illuminate\Support\Facades\Schema::getColumnListing($model->getTable());
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing($model->getTable());

    // remove items from columns with $removeProperties
    foreach ($removeProperties as $removeProperty) {
      if (in_array($removeProperty, $columns)) {
        $columns = array_diff($columns, [$removeProperty]);
      }
    }

    return $columns;
  }
}
