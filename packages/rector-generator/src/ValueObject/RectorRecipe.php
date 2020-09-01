<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\ValueObject;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Util\StaticRectorStrings;
use Rector\RectorGenerator\Exception\ConfigurationException;

final class RectorRecipe
{
    /**
     * @var string
     */
    private const PACKAGE_UTILS = 'Utils';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $codeBefore;

    /**
     * @var string
     */
    private $codeAfter;

    /**
     * @var bool
     */
    private $isRectorRepository = false;

    /**
     * @var bool
     */
    private $isPhpSnippet = true;

    /**
     * @var string
     */
    private $category;

    /**
     * @var class-string[]
     */
    private $nodeTypes = [];

    /**
     * @var mixed[]
     */
    private $resources = [];

    /**
     * @var mixed[]
     */
    private $configuration = [];

    /**
     * Use default package name, if not overriden manually
     * @var string
     */
    private $package = self::PACKAGE_UTILS;

    /**
     * @var string|null
     */
    private $set;

    /**
     * @var string|null
     */
    private $extraFileName;

    /**
     * @var string|null
     */
    private $extraFileContent;

    public function __construct(
        string $name,
        array $nodeTypes,
        string $description,
        string $codeBefore,
        string $codeAfter
    ) {
        $this->isRectorRepository = file_exists(__DIR__ . '/../../../../vendor');

        $this->setName($name);
        $this->setNodeTypes($nodeTypes);

        $this->description = $description;

        $this->setCodeBefore($codeBefore);
        $this->setCodeAfter($codeAfter);

        $this->resolveCategory($nodeTypes);
    }

    public function getPackage(): string
    {
        return $this->package;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNodeTypes(): array
    {
        return $this->nodeTypes;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCodeBefore(): string
    {
        return $this->codeBefore;
    }

    public function getCodeAfter(): string
    {
        return $this->codeAfter;
    }

    public function getResources(): array
    {
        return $this->resources;
    }

    public function setSet(string $set): void
    {
        $this->set = $set;
    }

    public function getSet(): ?string
    {
        return $this->set;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function getExtraFileName(): ?string
    {
        return $this->extraFileName;
    }

    public function getExtraFileContent(): ?string
    {
        return $this->extraFileContent;
    }

    public function isPhpSnippet(): bool
    {
        return $this->isPhpSnippet;
    }

    public function isRectorRepository(): bool
    {
        return $this->isRectorRepository;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getPackageDirectory(): string
    {
        if (! $this->isRectorRepository) {
            return 'rector';
        }

        // special cases
        if ($this->getPackage() === 'PHPUnit') {
            return 'phpunit';
        }

        return StaticRectorStrings::camelCaseToDashes($this->getPackage());
    }

    /**
     * @api
     */
    public function setPackage(string $package): void
    {
        $this->package = $package;
    }

    /**
     * @api
     */
    public function addExtraFile(string $extraFileName, string $extraFileContent): void
    {
        $this->extraFileName = $extraFileName;
        $this->extraFileContent = $this->normalizeCode($extraFileContent);
    }

    /**
     * @api
     * @param mixed[] $configuration
     */
    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * @api
     * @param string[] $resources
     */
    public function setResources(array $resources): void
    {
        $this->resources = array_filter($resources);
    }

    /**
     * For testing purposes
     * @api
     */
    public function setIsRectorRepository(bool $isRectorRepository): void
    {
        $this->isRectorRepository = $isRectorRepository;
    }

    private function setName(string $name): void
    {
        if (! Strings::endsWith($name, 'Rector')) {
            $message = sprintf('Rector name "%s" must end with "Rector"', $name);
            throw new ConfigurationException($message);
        }

        $this->name = $name;
    }

    /**
     * @param class-string[] $nodeTypes
     */
    private function setNodeTypes(array $nodeTypes): void
    {
        foreach ($nodeTypes as $nodeType) {
            if (is_a($nodeType, Node::class, true)) {
                continue;
            }

            $message = sprintf(
                'Node type "%s" does not exist, implement "%s" interface or is not imported in "rector-recipe.php"',
                $nodeType,
                Node::class
            );
            throw new ShouldNotHappenException($message);
        }

        if (count($nodeTypes) < 1) {
            $message = sprintf('"$nodeTypes" argument requires at least one item, e.g. "%s"', FuncCall::class);
            throw new ConfigurationException($message);
        }

        $this->nodeTypes = $nodeTypes;
    }

    private function setCodeBefore(string $codeBefore): void
    {
        $this->detectIsPhpSnippet($codeBefore);

        $this->codeBefore = $this->normalizeCode($codeBefore);
    }

    private function setCodeAfter(string $codeAfter): void
    {
        $this->codeAfter = $this->normalizeCode($codeAfter);
    }

    /**
     * @param string[] $nodeTypes
     */
    private function resolveCategory(array $nodeTypes): void
    {
        $this->category = (string) Strings::after($nodeTypes[0], '\\', -1);
    }

    private function detectIsPhpSnippet(string $codeBefore): void
    {
        // dummy yet effective way to detect PHP snippet from HTML
        $this->isPhpSnippet = Strings::startsWith($codeBefore, '<?php') || Strings::match($codeBefore, '#^class #ms');
    }

    private function normalizeCode(string $code): string
    {
        if (Strings::startsWith($code, '<?php')) {
            $code = ltrim($code, '<?php');
        }

        return trim($code);
    }
}
