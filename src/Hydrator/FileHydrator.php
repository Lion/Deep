<?php

namespace rsanchez\Deep\Hydrator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use rsanchez\Deep\Model\Entry;
use rsanchez\Deep\Hydrator\AbstractHydrator;
use rsanchez\Deep\Model\File;

class FileHydrator extends AbstractHydrator
{
    protected $files;

    public function preload(Collection $collection)
    {
        $this->files = File::with('uploadPref')->fromEntryCollection($collection)->get();
    }

    public function hydrate(Collection $collection, Entry $entry)
    {
        $files = $this->files;

        // loop through all file fields
        $entry->channel->fieldsByType('file')->each(function ($field) use ($entry, $files) {

            $entry->setAttribute($field->field_name, $files->filter(function ($file) use ($entry, $field) {
                return $entry->getAttribute('field_id_'.$field->field_id) === '{filedir_'.$file->upload_location_id.'}'.$file->file_name;
            })->first());

        });

        // loop through all matrix fields
        $entry->channel->fieldsByType('matrix')->each(function ($field) use ($collection, $entry, $files) {

            $entry->getAttribute($field->field_name)->each(function ($row) use ($collection, $entry, $files, $field) {

                $cols = $collection->getMatrixCols()->filter(function ($col) use ($field) {
                    return $col->field_id === $field->field_id && $col->col_type === 'file';
                });

                $cols->each(function ($col) use ($entry, $field, $row, $files) {
                    $row->setAttribute($col->col_name, $files->filter(function ($file) use ($entry, $field, $row, $col) {
                        return $row->getAttribute('col_id_'.$col->col_id) === '{filedir_'.$file->upload_location_id.'}'.$file->file_name;
                    })->first());
                });

            });

        });

        // loop through all grid fields
        $entry->channel->fieldsByType('grid')->each(function ($field) use ($collection, $entry, $files) {

            $entry->getAttribute($field->field_name)->each(function ($row) use ($collection, $entry, $files, $field) {

                $cols = $collection->getGridCols()->filter(function ($col) use ($field) {
                    return $col->field_id === $field->field_id && $col->col_type === 'file';
                });

                $cols->each(function ($col) use ($entry, $field, $row, $files) {
                    $row->setAttribute($col->col_name, $files->filter(function ($file) use ($entry, $field, $row, $col) {
                        return $row->getAttribute('col_id_'.$col->col_id) === '{filedir_'.$file->upload_location_id.'}'.$file->file_name;
                    })->first());
                });

            });

        });
    }
}
