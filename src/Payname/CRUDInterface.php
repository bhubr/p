<?php
/**
 * Common interface for CRUD operations
 */

namespace Payname;

/**
 * Define CRUD operations
 */
interface CRUDInterface {

	/**
	 * Create
	 */
	public static function create($props = []);

	/**
	 * Get one
	 */
	public static function get($hash);

	/**
	 * Update
	 */
	 public function update();

	/**
	 * Delete
	 */
	 public function delete();
}