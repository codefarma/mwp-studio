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
class BaseAnalyzer extends AbstractAnalyzer
{	
	/**
	 * Before traversing
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
		
		if ( $node instanceof Node\Stmt\Class_ or $node instanceof Node\Stmt\Trait_ ) {
			$this->getTraverser()->setCurrentClassname( $node->name );
		}
		
		if ( $node instanceof Node\Stmt\TraitUse ) {
			foreach( $node->traits as $trait ) {
				$this->getTraverser()->addClassUses( implode( '\\', $trait->parts ) );
			}
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
		
		if ( $node instanceof Node\Stmt\Class_ or $node instanceof Node\Stmt\Trait_ ) {
			$node->traitsUsed = $this->getTraverser()->getClassUses();
			$this->getTraverser()->setCurrentClassname('');
		}
		
		return $node;
    }
	
	/**
	 * After traverse
	 *
	 * @return	void
	 */
	public function afterTraverse( array $nodes )
	{

	}
}
