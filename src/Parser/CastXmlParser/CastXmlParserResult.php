<?php

declare(strict_types=1);

namespace FFI\Generator\Parser\CastXmlParser;

use FFI\Generator\Node\NamespaceNode;
use FFI\Generator\Node\NodeInterface;
use FFI\Generator\Node\Type\TypeInterface;
use FFI\Generator\Node\Type\UnknownTypeNode;
use FFI\Generator\Parser\CastXmlParser\Builder\ArrayTypeNodeTypeBuilder;
use FFI\Generator\Parser\CastXmlParser\Builder\BuilderInterface;
use FFI\Generator\Parser\CastXmlParser\Builder\CvQualifiedTypeNodeTypeBuilder;
use FFI\Generator\Parser\CastXmlParser\Builder\ElaboratedTypeNodeTypeBuilder;
use FFI\Generator\Parser\CastXmlParser\Builder\EnumerationTypeNodeTypeBuilder;
use FFI\Generator\Parser\CastXmlParser\Builder\FunctionNodeBuilder;
use FFI\Generator\Parser\CastXmlParser\Builder\FunctionTypeNodeTypeBuilder;
use FFI\Generator\Parser\CastXmlParser\Builder\FundamentalTypeNodeTypeBuilder;
use FFI\Generator\Parser\CastXmlParser\Builder\NamespaceNodeBuilder;
use FFI\Generator\Parser\CastXmlParser\Builder\PointerTypeNodeTypeBuilder;
use FFI\Generator\Parser\CastXmlParser\Builder\StructTypeNodeBuilder;
use FFI\Generator\Parser\CastXmlParser\Builder\TypeDefinitionTypeNodeTypeBuilder;
use FFI\Generator\Parser\CastXmlParser\Builder\UnimplementedTypeNodeBuilder;
use FFI\Generator\Parser\CastXmlParser\Builder\UnionTypeNodeBuilder;
use FFI\Generator\Parser\CastXmlParser\Builder\VariableNodeBuilder;
use FFI\Generator\Parser\CastXmlParser\CastXmlParserResult\CastXmlDocument;
use FFI\Generator\Parser\CastXmlParser\CastXmlParserResult\CastXmlFileStorage;

/**
 * @template-implements \IteratorAggregate<array-key, NamespaceNode>
 */
final class CastXmlParserResult implements
    BuilderInterface,
    \IteratorAggregate
{
    private readonly CastXmlDocument $document;

    /**
     * @var array<non-empty-string, TypeInterface>
     */
    private array $types = [];

    /**
     * @var list<BuilderInterface>
     */
    private array $builders = [];

    private CastXmlFileStorage $files;

    public function __construct(\DOMDocument $dom)
    {
        $this->document = new CastXmlDocument($dom);
        $this->files = new CastXmlFileStorage($this->document);

        $this->builders = [
            new EnumerationTypeNodeTypeBuilder($this, $this->files, $this->document),
            new TypeDefinitionTypeNodeTypeBuilder($this, $this->files, $this->document),
            new StructTypeNodeBuilder($this, $this->files, $this->document),
            new FundamentalTypeNodeTypeBuilder($this, $this->files, $this->document),
            new PointerTypeNodeTypeBuilder($this, $this->files, $this->document),
            new ElaboratedTypeNodeTypeBuilder($this, $this->files, $this->document),
            new FunctionTypeNodeTypeBuilder($this, $this->files, $this->document),
            new CvQualifiedTypeNodeTypeBuilder($this, $this->files, $this->document),
            new UnionTypeNodeBuilder($this, $this->files, $this->document),
            new ArrayTypeNodeTypeBuilder($this, $this->files, $this->document),
            new FunctionNodeBuilder($this, $this->files, $this->document),
            new VariableNodeBuilder($this, $this->files, $this->document),
            new UnimplementedTypeNodeBuilder($this, $this->files, $this->document),
            new NamespaceNodeBuilder($this, $this->files, $this->document),
        ];
    }

    public function create(\DOMElement $el): NodeInterface
    {
        $id = $el->getAttribute('id');

        if ($id === '') {
            throw new \InvalidArgumentException('Definition ID cannot be empty');
        }

        if (isset($this->types[$id])) {
            return $this->types[$id];
        }

        foreach ($this->builders as $builder) {
            if ($builder->supports($el->nodeName)) {
                $instance = $this->types[$id] = $builder->create($el);

                new \ReflectionObject($instance)
                    ->initializeLazyObject($instance);

                return $instance;
            }
        }

        return new UnknownTypeNode($el->nodeName);
    }

    public function supports(string $name): bool
    {
        foreach ($this->builders as $builder) {
            if ($builder->supports($name)) {
                return true;
            }
        }

        return false;
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->document->getElementsByTagName('Namespace') as $namespace) {
            yield $this->create($namespace);
        }
    }
}
