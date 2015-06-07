<?php namespace SimpleLocator\Services\Import\Events;

use SimpleLocator\Services\Import\Listeners\ImportFileHandler;
use SimpleLocator\Services\Import\Listeners\ImportGetRowHandler;
use SimpleLocator\Services\Import\Listeners\ImportMapColumnsHandler;
use SimpleLocator\Services\Import\Listeners\ImportHandler;
use SimpleLocator\Services\Import\Listeners\ImportFinishHandler;


/**
* Register Events Related to Imports
*/
class RegisterImportEvents {

	public function __construct()
	{
		// Import Handlers
		add_action( 'admin_post_wpslimportupload', array($this, 'wpsl_import_file'));
		add_action( 'wp_ajax_wpslimportcolumns', array($this, 'wpsl_import_columns' ));
		add_action( 'admin_post_wpslmapcolumns', array($this, 'wpsl_map_columns'));
		add_action( 'wp_ajax_wpsldoimport', array($this, 'wpsl_do_import' ));
		add_action( 'wp_ajax_wpslfinishimport', array($this, 'wpsl_finish_import'));
	}

	/**
	* Import File Handler
	*/
	public function wpsl_import_file()
	{
		new ImportFileHandler;
	}

	/**
	* Get the CSV columns for mapping
	*/
	public function wpsl_import_columns()
	{
		new ImportGetRowHandler;
	}

	/**
	* Map the columns for import
	*/
	public function wpsl_map_columns()
	{
		new ImportMapColumnsHandler;
	}

	/**
	* Do the import
	*/
	public function wpsl_do_import()
	{
		new ImportHandler;
	}

	/**
	* Finish the import
	*/
	public function wpsl_finish_import()
	{
		new ImportFinishHandler;
	}


}