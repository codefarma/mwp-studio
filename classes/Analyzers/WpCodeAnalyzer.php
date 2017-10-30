<?php
/**
 * Plugin Class File
 *
 * Created:   August 13, 2017
 *
 * @package:  MWP Studio
 * @author:   Kevin Carwile
 * @since:    0.0.0
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
