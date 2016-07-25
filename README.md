
#Yiiic - интерактивная консоль для Yii2
Интерактивная `yiiic` консоль реализует умный автокомплит и контекстный воркфлоу.

1. [Установка](#installation)
2. [Интеграция в проект](#integration)
3. [Умный автокомплит](#complete)
4. [Служебные комманды](#commands)
5. [Конфигурация](#configuration)

##<a name="installation">Установка</a> 
`composer require ltd-beget/yiiic`

##<a name="integration">Интеграция в проект</a>

Добавить `yiiic` компонент в конфиг консольного приложения
```php
$application = new yii\console\Application([
    ...
    'components' => [
        'yiiic' => [
            'class' => 'LTDBeget\Yiiic\Yiiic',
            'params' => []
        ]
    ]
]);
```
Добавить консольный конроллер  как точку входа в интерактивный режим. Отнаследовать его от `LTDBeget\Yiiic\Controllers\YiiicController` либо создать `actionIndex` и в нем прописать код инициализации как в примере ниже.
```php
namespace LTDBeget\Yiiic\Controllers;

use yii\console\Controller;

use LTDBeget\Yiiic\ApiReflector;
use LTDBeget\Yiiic\ColFormatter;
use LTDBeget\Yiiic\Context;
use LTDBeget\Yiiic\Writer;
use LTDBeget\Yiiic\Yiiic;

class YiiicController extends Controller
{

    public function actionIndex()
    {
        $yiiic = \Yii::$app->get('yiiic');
        
		// Обязательные зависимости
        $yiiic->setReflection(new ApiReflector($yiiic->param('ignore')));
        $yiiic->setColFormatter(new ColFormatter());
        $yiiic->setWriter(new Writer());
        $yiiic->setContext(new Context());

		//Опционально
		 
	    //Если хочется комплит по агрументам пишем реализацию и инжектим
        $yiiic->setArgsCompleter(LTDBeget\Yiiic\ArgsCompleterInterface)	

        $yiiic->run();
    }

}
```

##<a name="complete">Умный автокомплит</a>
При работе с консолью, нажатие TAB вызывает автоподсказку возможных контроллеров/экшенов/опций.  При реализации интерфейса `ArgsCompleterInterface`  возможен комплит по аргументам. Автокомплит работает по контексту, то есть если вы ввели `migrate [press TAB]` и нажали таб, то получите список экшенов для `migrate`.

##<a name="commands">Служебные комманды</a>
- :c - перейти в контекст
- :h - помощь
- :q - выход

###`:c [controller] [action]`  
Изначально вы находитесь в контексте `yiiic`.  Можно перейти в контекст контроллера или экшена. Находясь в контексте мы уменьшаем количество ввода, для выполнения комманд одного контроллера или конкретных экшенов.

###`:h [controller] [action]` 

Для `:c` и `:h` можно указывать абсолютную нотацию `::`. При абсолютной нотации текущий контекст не учитывается. То есть если вы находитесь в контексте `migrate` и ввели `::с ` и нажали TAB, то автокомплит будет работать как будто контекста нет и вы получите список всех контроллеров.   

##<a name="configuration">Конфигурация</a>
Для всей конфигурации есть дефолтный preset, поэтому можно оставить `params = []`
```php
$application = new yii\console\Application([
    'components' => [
        'yiiic' => [
            'class' => 'LTDBeget\Yiiic\Yiiic',
            'params' => [
	            // не выводить в хелпе
	            'ignore' => ['yiiic', 'help'],
	            'prompt' => 'yiiic',
	            // можно выставить свои
	            'commands' => [
	                'context' => 'c',
	                'quit' => 'q',
	                'help' => 'h'
	            ],
	            //префикс для служебных комманд,
	            //удвоение дает работу без контекста
	            'command_prefix' => ':',
	            // высота в строках хелпа, если не будет
	            // помещаться, рассчитается так чтоб влезло
	            'height_help' => 5,
	            // можно выставить нескучный символ
	            // для результата выполнения экшена
	            'result_border' => '=',
	            // стили для стильных
	            'style' => [
	                'prompt' => [Console::FG_GREEN, Console::BOLD],
	                'welcome' => [Console::FG_YELLOW, Console::BOLD],
	                'bye' => [Console::FG_YELLOW, Console::BOLD],
	                'notice' => [Console::FG_YELLOW, Console::BOLD],
	                'error' => [Console::BG_RED],
	                'help' => [
	                    'title' => [Console::FG_YELLOW, Console::UNDERLINE],
	                    'scope' => [Console::FG_YELLOW, Console::ITALIC]
	                ],
	                'result' => [
	                    'border' => [Console::FG_CYAN]
	                ]
	            ]
            ]
        ]
    ]
]);
```