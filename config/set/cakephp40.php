<?php

declare(strict_types=1);

use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector;
use Rector\CakePHP\ValueObject\CallWithParamRename;
use Rector\CakePHP\ValueObject\UnprefixedMethodToGetSet;
use Rector\Generic\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\Generic\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Generic\ValueObject\MethodReturnType;
use Rector\Generic\ValueObject\RenamedProperty;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstantRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\ClassConstantRename;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\StaticCallRename;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\ParameterTypehint;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# source: https://book.cakephp.org/4/en/appendices/4-0-migration-guide.html

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'Cake\Database\Type' => 'Cake\Database\TypeFactory',
                'Cake\Console\ConsoleErrorHandler' => 'Cake\Error\ConsoleErrorHandler',
            ],
        ]]);

    $services->set(RenameClassConstantRector::class)
        ->call('configure', [[
            RenameClassConstantRector::CLASS_CONSTANT_RENAME => inline_value_objects([
                new ClassConstantRename('Cake\View\View', 'NAME_ELEMENT', 'TYPE_ELEMENT'),
                new ClassConstantRename('Cake\View\View', 'NAME_LAYOUT', 'TYPE_LAYOUT'),
                new ClassConstantRename('Cake\Mailer\Email', 'MESSAGE_HTML', 'Cake\Mailer\Message::MESSAGE_HTML'),
                new ClassConstantRename('Cake\Mailer\Email', 'MESSAGE_TEXT', 'Cake\Mailer\Message::MESSAGE_TEXT'),
                new ClassConstantRename('Cake\Mailer\Email', 'MESSAGE_BOTH', 'Cake\Mailer\Message::MESSAGE_BOTH'),
                new ClassConstantRename('Cake\Mailer\Email', 'EMAIL_PATTERN', 'Cake\Mailer\Message::EMAIL_PATTERN'),
            ]),
        ]]);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => inline_value_objects([
                new MethodCallRename('Cake\Form\Form', 'errors', 'getErrors'),
                new MethodCallRename('Cake\Mailer\Email', 'set', 'setViewVars'),
                new MethodCallRename('Cake\ORM\EntityInterface', 'unsetProperty', 'unset'),
                new MethodCallRename('Cake\Cache\Cache', 'engine', 'pool'),
                new MethodCallRename('Cake\Http\Cookie\Cookie', 'getStringValue', 'getScalarValue'),
                new MethodCallRename('Cake\Validation\Validator', 'containsNonAlphaNumeric', 'notAlphaNumeric'),
                new MethodCallRename('Cake\Validation\Validator', 'errors', 'validate'),
            ]),
        ]]);

    $services->set(RenameStaticMethodRector::class)
        ->call('configure', [[
            RenameStaticMethodRector::OLD_TO_NEW_METHODS_BY_CLASSES => inline_value_objects([
                new StaticCallRename('Router', 'pushRequest', 'Router', 'setRequest'),
                new StaticCallRename('Router', 'setRequestInfo', 'Router', 'setRequest'),
                new StaticCallRename('Router', 'setRequestContext', 'Router', 'setRequest'),
            ]),
        ]]);

    $services->set(RenamePropertyRector::class)
        ->call('configure', [[
            RenamePropertyRector::RENAMED_PROPERTIES => inline_value_objects([
                new RenamedProperty('Cake\ORM\Entity', '_properties', '_fields'),
            ]),
        ]]);

    $services->set(AddReturnTypeDeclarationRector::class)
        ->call('configure', [[
            AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => inline_value_objects([
                new MethodReturnType('Cake\Http\BaseApplication', 'bootstrap', 'void'),
                new MethodReturnType('Cake\Http\BaseApplication', 'bootstrapCli', 'void'),
                new MethodReturnType('Cake\Http\BaseApplication', 'middleware', 'Cake\Http\MiddlewareQueue'),
                new MethodReturnType('Cake\Console\Shell', 'initialize', 'void'),
                new MethodReturnType('Cake\Controller\Component', 'initialize', 'void'),
                new MethodReturnType('Cake\Controller\Controller', 'initialize', 'void'),
                new MethodReturnType('Cake\Controller\Controller', 'render', 'Cake\Http\Response'),
                new MethodReturnType('Cake\Form\Form', 'validate', 'bool'),
                new MethodReturnType('Cake\Form\Form', '_buildSchema', 'Cake\Form\Schema'),
                new MethodReturnType('Cake\ORM\Behavior', 'initialize', 'void'),
                new MethodReturnType('Cake\ORM\Table', 'initialize', 'void'),
                new MethodReturnType('Cake\ORM\Table', 'updateAll', 'int'),
                new MethodReturnType('Cake\ORM\Table', 'deleteAll', 'int'),
                new MethodReturnType('Cake\ORM\Table', 'validationDefault', 'Cake\Validation\Validator'),
                new MethodReturnType('Cake\ORM\Table', 'buildRules', 'Cake\ORM\RulesChecker'),
                new MethodReturnType('Cake\View\Helper', 'initialize', 'void'), ]),
        ]]);

    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => inline_value_objects([
                new ParameterTypehint('Cake\Form\Form', 'getData', 0, '?string'),
                new ParameterTypehint('Cake\ORM\Behavior', 'beforeFind', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\ORM\Behavior', 'buildValidator', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\ORM\Behavior', 'buildRules', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\ORM\Behavior', 'beforeRules', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\ORM\Behavior', 'afterRules', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\ORM\Behavior', 'beforeSave', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\ORM\Behavior', 'afterSave', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\ORM\Behavior', 'beforeDelete', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\ORM\Behavior', 'afterDelete', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\ORM\Table', 'beforeFind', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\ORM\Table', 'buildValidator', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\ORM\Table', 'buildRules', 0, 'Cake\ORM\RulesChecker'),
                new ParameterTypehint('Cake\ORM\Table', 'beforeRules', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\ORM\Table', 'afterRules', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\ORM\Table', 'beforeSave', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\ORM\Table', 'afterSave', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\ORM\Table', 'beforeDelete', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\ORM\Table', 'afterDelete', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\Controller\Controller', 'beforeFilter', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\Controller\Controller', 'afterFilter', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\Controller\Controller', 'beforeRender', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint(
                    'Cake\Controller\Controller',
                    'beforeRedirect',
                    0,
                    'Cake\Event\EventInterface'
                ),
                new ParameterTypehint('Cake\Controller\Component', 'shutdown', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\Controller\Component', 'startup', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\Controller\Component', 'beforeFilter', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\Controller\Component', 'beforeRender', 0, 'Cake\Event\EventInterface'),
                new ParameterTypehint('Cake\Controller\Component', 'beforeRedirect', 0, 'Cake\Event\EventInterface'),
            ]),
        ]]);

    $services->set(RenameMethodCallBasedOnParameterRector::class)
        ->call('configure', [[
            RenameMethodCallBasedOnParameterRector::CALLS_WITH_PARAM_RENAMES => inline_value_objects([
                new CallWithParamRename('Cake\Http\ServerRequest', 'getParam', 'paging', 'getAttribute'),
                new CallWithParamRename('Cake\Http\ServerRequest', 'withParam', 'paging', 'withAttribute'),
            ]),
        ]]);

    $services->set(ModalToGetSetRector::class)
        ->call('configure', [[
            ModalToGetSetRector::UNPREFIXED_METHODS_TO_GET_SET => inline_value_objects([
                new UnprefixedMethodToGetSet('Cake\Console\ConsoleIo', 'styles', 'setStyle', 'getStyle'),
                new UnprefixedMethodToGetSet('Cake\Console\ConsoleOutput', 'styles', 'setStyle', 'getStyle'),
                new UnprefixedMethodToGetSet('Cake\ORM\EntityInterface', 'isNew', 'setNew', 'isNew'),
            ]),
        ]]);
};
