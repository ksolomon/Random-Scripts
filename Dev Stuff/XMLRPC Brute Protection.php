<?php
/*
Plugin Name: XML-RPC Brute Protection
Description: Disable XML-RPC methods used in brute-force amplification attacks
Author: Keith Solomon
Version: 1.0
License: GPL2
*/


function remove_xmlrpc_methods( $methods ) {
	unset( $methods['system.multicall'] );
	unset( $methods['system.listMethods'] );
	unset( $methods['system.getCapabilities']);

	return $methods;
}

add_filter( 'xmlrpc_methods', 'remove_xmlrpc_methods' );
