
#Yiiic - yii interactive console

###Commands
Могут быть/не быть контексто-зависимыми. 

`КЗК` - контексто-зависимая комманда, результат и автокомплит зависят от текущего контекста

`КНК` - контексто-НЕзависимая комманда, результат и автокомплит НЕ зависят от текущего контекста

####КЗК
`-q` - выход

`? [controller] [action]` - получить помощь по комманде

####КЗН
`[controller] [action] -c` - перейти в указанный контекст
 
`[controller] [action] -b` - выйти из контекста

###Configuration
Для всей конфигурации есть дефолтный preset, поэтому можно оставить `params = []`
```php
$application = new yii\console\Application([
    'components' => [
        'yiiic' => [
            'class' => 'Yiiic\Yiiic',
            'params' => [
	            // не отображать в хелпе
	            'ignore'        => ['interactive', 'help'],
	            // служебные комманды
	            'commands'      => [
	                'context_enter' => '-c',
	                'context_quit'  => '-b',
	                'quit'          => '-q',
	                'help'          => '?'
	            ],
	            // высота хелпа
	            'height_help'   => 5,
	            // бордер для результата
	            'result_border' => '=',
	            // стили для стильных
	            'style'         => [
	                'welcome' => [Console::FG_YELLOW, Console::BOLD],
	                'bye'     => [Console::FG_YELLOW, Console::BOLD],
	                'notice'  => [Console::FG_YELLOW, Console::BOLD],
	                'help'    => [
	                    'title' => [Console::FG_YELLOW, Console::UNDERLINE],
	                    'scope' => [Console::FG_YELLOW, Console::ITALIC]
	                ],
	                'result'  => [
	                    'border' => [Console::FG_CYAN]
	                ],
	                'error'   => [Console::BG_RED]
	            ]
	        ]
        ]
    ]
]);
```
