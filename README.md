
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

Добавить `yiiic` компонент в конфиг консольного приложения, в `build` передается массив параметров
```php
$application = new yii\console\Application([
    ...
    'components' => [
	    //Для реализации чего то недефолтного пишем свой build с инжектом
	    //своих реализаций
        'yiiic' => LTDBeget\Yiiic\build()
    ]
]);
```
Отнаследовать консольный конроллер  как точку входа в интерактивный режим от `\LTDBeget\Yiiic\YiiicController`
```php
use yii\console\Controller;

class YiiicController extends \LTDBeget\Yiiic\YiiicController
{

}
```

##<a name="complete">Умный автокомплит</a>
При работе с консолью, нажатие TAB вызывает автоподсказку возможных контроллеров/экшенов/опций.  При реализации интерфейса `ArgsCompleterInterface`  возможен комплит по аргументам. Автокомплит работает по контексту, то есть если вы ввели `migrate [press TAB]` и нажали таб, то получите список экшенов для `migrate`.

##<a name="commands">Служебные комманды</a>
- c - перейти в контекст
- h - помощь
- q - выход

Комманды можно передавать в любом месте, следующие вызовы равнозначны
```bash
migrate create c
c migrate create
```
Для запуска комманды без контекста используется префикс `/`

##<a name="configuration">Конфигурация</a>
Конфигурация в  порядке возрастания приоритета  `[default] <- [build()] <- [cli config] <- [cli option]`
-  default - дефолтный пресет
-  build() - то что передается в билд функцию/метод
- cli config - путь к конфигу при запуске yiiic режима (`yii yiiic --config=custom/config/path`) 
- cli option - значение конкретного опшена, список доступных в хелпе

Дефолтный пресет
```php
// config.php

return [
    // не выводить в хелпе
    'ignore' => ['yiiic', 'help'],
    'prompt' => 'yiiic',
    'show_help' => Configuration::SHOW_HELP_*,
    // если вылезет exception
    'show_trace' => false,
    // можно выставить свои
    'commands' => [
        'context' => 'c',
        'quit' => 'q',
        'help' => 'h'
    ],
    'without_context_prefix' => '/',
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
];
```