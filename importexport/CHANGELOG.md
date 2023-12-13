# 0.1.0

## New Features

* Users can define column mappings via CSVFieldMapper / GridFieldImporter. Mappings are saved for future imports.
* CSV files can be previewed via the CSVPreviewer class.
* Records can be skipped during import. Skipped records are recorded in result object.
* Introduced BulkLoaderSource as a way of abstracting CSV / other source functionality away from the BulkLoader class.
* Introduced ListBulkLoader for confining record CRUD actions to a given DataList (HasManyList).
* Decoupled CSVParser from BulkLoader. Column mapping is now performed in BulkLoader on each record as it is loaded.
* Replaced CSVParser with goodby/csv library.
* Customisation control over fields for which relation objects are created and/or linked.

## Removed Features

* No longer can you specify a callback by giving the name of the callback, it must now be an anonmyous function. This includes using '->', and 'importFunctionName' to specify functions that are written on the $obj, or on a subclass of the loader.

## Bug Fixes

* Validation failing on DataObject->write() will cause a record to be skipped, rather than halting the whole process.
* Prevented bulk loader from trying to work with relation names that don't exist. This would particularly cause issues when CSV header names contained a ".".

## Upgrading Notes

* You'll need to seperately define a BulkLoaderSource when configuring your BulkLoader. 
