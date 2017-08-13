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
class BaseAnalyzer extends AbstractAnalyzer
{	
	/**
	 * Entering a node
	 *
	 * @return	void
	 */
    public function beforeTraverse( array $nodes ) 
	{
	
    }
	
	/**
	 * Entering a node
	 *
	 * @return	void
	 */
    public function enterNode( Node $node ) 
	{
		if ( $node instanceof Node\Stmt\Namespace_ ) {
			$this->getTraverser()->setCurrentNamespace( $node->name->parts );
		}
		
		if ( $node instanceof Node\Stmt\Class_ ) {
			$this->getTraverser()->setCurrentClassname( $node->name );
		}
    }
	
	/**
	 * Leaving a node
	 *
	 * @return	void
	 */
    public function leaveNode( Node $node ) 
	{
		if ( $node instanceof Node\Stmt\Namespace_ ) {
			$this->getTraverser()->setCurrentNamespace( array() );
		}
		
		if ( $node instanceof Node\Stmt\Class_ ) {
			$this->getTraverser()->setCurrentClassname( '' );
		}
    }
	
}
