<?php
/*
Plugin Name: Cart66 Cloud - Members
Plugin URI: http://cart66.com
Description: Membership functionality for Cart66 Cloud
Version: 1.1.6
Author: Reality66
Author URI: http://www.reality66.com

-------------------------------------------------------------------------
Cart66 Cloud Members
Copyright 2016  Reality66

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

include_once 'includes/cm-functions.php';
include_once 'includes/class-cm-cart66-members.php';

// Execute with priority 20 so the members plugin loads after the core plugin
add_action( 'plugins_loaded', array( 'CM_Cart66_Members', 'get_instance'), 20 );
