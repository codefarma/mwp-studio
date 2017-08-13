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

/**
 * File Class
 */
class WpCodeAnalyzer extends AbstractAnalyzer
{	
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
					
					/**
					 * Analyze the callback for certain hooks
					 */
					if ( in_array( $func_name, array( 'add_filter', 'add_action' ) ) )
					{
						$callback = $node->args[1]->value;
						
						/**
						 * Function name provided
						 */
						if ( $callback instanceof Node\Expr\String_ )
						{
							$callback_type = 'function';
							$callback_name = $callback->value;
						}
						
						/**
						 * Anonymous function provided
						 */
						else if ( $callback instanceof Node\Expr\Closure ) 
						{
							$callback_type = 'closure';
						}
						
						/**
						 * Class/method array provided
						 */
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
				
					$this->data['hooks'][] = array_merge
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
							'args' => 0,
							'priority' => 10,
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
				if ( preg_match_all( '/@Wordpress\\\(Action|Filter)\((.*)for="(.+)"(.*)\)/sU', $docBlock->getText(), $matches ) ) {
					foreach( $matches[0] as $i => $match ) {
						$this->data['hooks'][] = array_merge(
							$this->getTraverser()->getCurrentFileInfo(),
							array(
								'type' => 'add_' . strtolower($matches[1][$i]),
								'name' => $matches[3][$i],
								'callback_name' => $node->name,
								'callback_class' => $this->getTraverser()->getCurrentClassname(),
								'callback_type' => 'method',
								'line' => $docBlock->getLine(),
								'data' => array( 'register_type' => 'annotation' ),
								'args' => 0,
								'priority' => 10,
								'catalog_time' => time(),
							)
						);
					}
				}
			}
		}
    }
	
}
