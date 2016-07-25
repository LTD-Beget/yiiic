
#Yiiic - интерактивная консоль для Yii2
Интерактивная `yiiic` консоль реализует умный автокомплит и контекстный воркфлоу.

##Умный автокомплит
При работе с консолью, нажатие TAB вызывает автоподсказку возможных контроллеров/экшенов/опций.  При реализации интерфейса `ArgsCompleterInterface`  возможен комплит по аргументам. Автокомплит работает по контексту, то есть если вы ввели `migrate [press TAB]` и нажали таб, то получите список экшенов для `migrate`.

##Контекстный воркфлоу `:c [controller] [action]`  
Изначально вы находитесь в контексте `yiiic`.  Можно перейти в контекст контроллера или экшена. Находясь в контексте мы уменьшаем количество ввода, для выполнения комманд одного контроллера или конкретных экшенов.

##Помощь `:h [controller] [action]` 

Для `:c` и `:h` можно указывать абсолютную нотацию `::`. При абсолютной нотации текущий контекст не учитывается. То есть если вы находитесь в контексте `migrate` и ввели `::с ` и нажали TAB, то автокомплит будет работать как будто контекста нет и вы получите список всех контроллеров.  

##Выход `:q` 

##Настройки
Для всей конфигурации есть дефолтный preset, поэтому можно оставить `params = []`
```php
$application = new yii\console\Application([
    'components' => [
        'yiiic' => [
            'class' => 'LTDBeget\Yiiic\Yiiic',
            'params' => [
	            // не выводить в хелпе
	            'ignore' => ['yiiic', 'help'],
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
##Установка 
`composer require ltd-beget/yiiic`

##Интеграция в проект
1. Добавить `yiiic` компонент в конфиг консольного приложения
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
2. Добавить консольный конроллер  как точку входа в интерактивный режим. Отнаследовать его от `LTDBeget\Yiiic\Controllers\YiiicController` либо создать `actionIndex` и в нем прописать код инициализации как в примере ниже.

 ```php
 class YiiicController extends Controller
{

        public function actionIndex()
        {
            /**
             * @var Yiiic $yiiic
             */
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
