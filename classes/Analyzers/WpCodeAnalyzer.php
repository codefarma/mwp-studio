<?php
/**
 * Plugin Class File
 *
 * Created:   August 13, 2017
 *
 * @package:  Wordpress Plugin Studio
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace MWP\Studio\Analyzers;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;

function do_or_die()
{
}

/**
 * File Class
 */
class WpCodeAnalyzer extends AbstractAnalyzer
{
	/**
	 * Before traverse
	 */
	public function beforeTraverse( array $nodes )
	{
		$this->analysis['files'][] = $this->getTraverser()->getCurrentFileInfo();
	}
	 
	/**
	 * Entering a node
	 *
	 * @return	void
	 */
    public function enterNode( Node $node ) 
	{
		/**
		 * Natural hooks & filters
		 */
        if ( $node instanceof Node\Expr\FuncCall ) 
		{
			$func_name = $node->name->parts[0];
			
			/**
			 * apply_filters, do_action, add_filter, add_action
			 */
			if ( in_array( $func_name, array( 'apply_filters', 'do_action', 'add_filter', 'add_action' ) ) )
			{
				$hook = $node->args[0]->value;
				if ( $hook instanceof Node\Scalar\String_ ) 
				{
					$callback_name = null;
					$callback_class = null;
					$callback_type = null;
					$hook_priority = null;
					$hook_args = null;
					
					/**
					 * Hooks that register callbacks
					 */
					if ( in_array( $func_name, array( 'add_filter', 'add_action' ) ) )
					{
						$callback = $node->args[1]->value;
						
						/**
						 * Analyze Callback
						 */
						{
							// Function name provided
							if ( $callback instanceof Node\Scalar\String_ )
							{
								if ( strpos( $callback->value, '::' ) ) {
									list( $classname, $method ) = explode( '::', $callback->value, 2 );
									$callback_type = 'method';
									$callback_name = $method;
									$callback_class = $classname;
								}
								else
								{
									$callback_type = 'function';
									$callback_name = $callback->value;
								}
							}
							
							// Anonymous function provided
							else if ( $callback instanceof Node\Expr\Closure ) 
							{
								$callback_type = 'closure';
							}
							
							// Class/method array provided
							else if ( $callback instanceof Node\Expr\Array_ ) 
							{
								$callback_type = 'method';
								
								$arg1 = $callback->items[0]->value;
								$arg2 = $callback->items[1]->value;
								
								if ( $arg1 instanceof Node\Scalar\String_ ) {
									$callback_class = $arg1->value;
								}
								else if ( $arg1 instanceof Node\Expr\Variable )
								{
									if ( $arg1->name == 'this' ) {
										$callback_class = $this->getTraverser()->getCurrentClassname();
									}
								}
								
								if ( $arg2 instanceof Node\Scalar\String_ ) {
									$callback_name = $arg2->value;
								}
							}
						}
						
						/**
						 * Analyze priority
						 *
						 * Use provided number priority for hook, or if not provided, use wordpress default value
						 */
						if ( isset( $node->args[2] ) ) {
							if ( $node->args[2]->value instanceof Node\Scalar\LNumber ) {
								$hook_priority = $node->args[2]->value->value;
							}
						} else {
							$hook_priority = 10;
						}
						
						/**
						 * Analyze arguments
						 *
						 * Use provided number of args for hook, or if not provided, use wordpress default value
						 */
						if ( isset( $node->args[3] ) ) {
							if ( $node->args[3]->value instanceof Node\Scalar\LNumber ) {
								$hook_args = $node->args[3]->value->value;
							}
						} else {
							$hook_args = 1;
						}
					}
					
					/**
					 * Hooks that register callbacks
					 */
					if ( in_array( $func_name, array( 'do_action', 'apply_filters' ) ) )
					{
						$hook_args = count( $node->args ) - 1;
					}
					
					/**
					 * Add hook details to analysis
					 */
					$this->analysis['hooks'][] = array_merge
					(
						$this->getTraverser()->getCurrentFileInfo(),
						array(
							'type' => $func_name,
							'name' => $hook->value,
							'callback_name' => $callback_name,
							'callback_class' => $callback_class,
							'callback_type' => $callback_type,
							'line' => $node->getLine(),
							'data' => array( 'register_type' => 'natural' ),
							'args' => $hook_args,
							'priority' => $hook_priority,
							'catalog_time' => time(),
						)
					);
				}
			}
        }
		
		/**
		 * Modern Wordpress Hooks & Filters
		 */
		if ( $node instanceof Node\Stmt\ClassMethod	) 
		{
			if ( $docBlock = $node->getDocComment() ) 
			{
				if ( preg_match_all( '/@Wordpress\\\(Action|Filter)\((.*)for="(.+)"(.*)\)/sU', $docBlock->getText(), $matches ) ) 
				{
					foreach( $matches[0] as $i => $match ) 
					{
						$hook_priority = 10;
						$hook_args = 1;
						
						if ( preg_match( '/priority=(\d+)/', $match, $m ) ) {
							$hook_priority = $m[1];
						}
						
						if ( preg_match( '/args=(\d+)/', $match, $m ) ) {
							$hook_args = $m[1];
						}
						
						$this->analysis['hooks'][] = array_merge
						(
							$this->getTraverser()->getCurrentFileInfo(),
							array(
								'type' => 'add_' . strtolower($matches[1][$i]),
								'name' => $matches[3][$i],
								'callback_name' => $node->name,
								'callback_class' => $this->getTraverser()->getCurrentClassname(),
								'callback_type' => 'method',
								'line' => $docBlock->getLine(),
								'data' => array( 'register_type' => 'annotation', 'annotation' => $match ),
								'args' => $hook_args,
								'priority' => $hook_priority,
								'catalog_time' => time(),
							)
						);
					}
				}
			}
		}
		
		/**
		 * Functions
		 */
		if ( $node instanceof Node\Stmt\Function_ or $node instanceof Node\Stmt\ClassMethod )
		{
			$this->analysis['functions'][] = array_merge
			(
				$this->getTraverser()->getCurrentFileInfo(),
				array(
					'name' => $node->name,
					'class' => $this->getTraverser()->getCurrentClassname(),
					'type' => $node instanceof Node\Stmt\ClassMethod ? ( $node->isAbstract() ? 'abstract' : 'method' ) : 'function',
					'args' => count( $node->params ),
					'data' => array(),
					'line' => $node->getLine(),
					'catalog_time' => time(),
				)
			);
		}		
	}
	
	/**
	 * Leaving a node
	 *
	 * @return	void
	 */
    public function leaveNode( Node $node ) 
	{
		/**
		 * Classes & traits
		 */
		if ( $node instanceof Node\Stmt\Class_ or $node instanceof Node\Stmt\Trait_ ) 
		{
			$this->analysis['classes'][] = array_merge
			(
				$this->getTraverser()->getCurrentFileInfo(),
				array(
					'name' => implode( '\\', $node->namespacedName->parts ),
					'extends' => isset( $node->extends ) ? implode( '\\', $node->extends->parts ) : null,
					'type' => $node instanceof Node\Stmt\Class_ ? 'class' : 'trait',
					'uses' => implode( ',', $node->traitsUsed ?: array() ),
					'data' => array(),
					'line' => $node->getLine(),
					'catalog_time' => time(),
				)
			);
		}
    }
	
}
