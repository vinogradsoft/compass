# Compass

> Библиотека работает с URL-адресами и с путями к файлам на диске. Compass написана на PHP и предлагает набор методов
> для обработки этих двух типов данных. Она может быть использована в различных приложениях, таких как веб-приложения
> для формирования и обработки путей к файлам или системы управления контентом для создания URL-адресов.

## Общая информация

Эта библиотека предназначена для использования в более высокоуровневых PHP-проектах и включает в себя два основных
компонента: для работы с путями к файлам (`\Compass\Path`) и для работы с URL (`\Compass\Url`). Оба эти
компонента представлены отдельными объектами и предлагают набор методов, упрощающих манипуляции с путями и URL-адресами.

`\Compass\Path` помогает упростить работу с директориями и файлами, позволяя находить и заменять определенные
директории по указанному шаблону, а также проверять наличие директорий в пути и изменять их порядок. Этот компонент
предоставляет возможность быстро и эффективно выполнять различные манипуляции с путями.

В свою очередь, `\Compass\Url` позволяет работать с URL, предоставляя набор методов для создания, получения и
изменения различных частей URL-адреса. Особенностью этого компонента является то, что он создает URL оптимальным
образом, изменяя только те части адреса, которые требуется изменить, при создании множества URL одного домена.

Схема показывает какие данные можно получить и модифицировать:

```
  |---------------------------------------absolute url---------------------------------|
  |                                                                                    |
  |-----------------base url----------------|------------------relative url------------|
  |                                         |                                          |
  |                    authority            |          path            query   fragment|
  |       /‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾\|/‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾\ /‾‾‾‾‾‾‾‾\ /‾‾‾‾‾‾\|
  |http://grigor:password@vinograd.soft:8080/path/to/resource.json?query=value#fragment|
   \__/   \___/  \_____/  \___________/ \__/                  \___/
  scheme  user   password     host      port                  suffix
```

Оба компонента работают по схожему принципу, сначала собирая необходимые параметры, а затем применяя их к
результату с помощью специального метода `updateSource()`.

## Установка

Предпочтительный способ установки - через [composer](http://getcomposer.org/download/).

Запустите команду

```
php composer require vinogradsoft/compass "^1.0"
```

Требуется PHP 8.0 или новее.

## Компонент URL

### Быстрый старт

```php
<?php

require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

use \Compass\Url;

$url = Url::createBlank();

$url->setScheme('https')
    ->setUser('grigor')
    ->setPassword('pass@word')
    ->setHost('host.ru')
    ->setPort('8088')
    ->setPath('/user/index')
    ->setSuffix('.php')
    ->setArrayQuery([
        'key1' => 'value1',
        'key2' => 'value2'
    ])->setFragment('fragment');

$url->updateSource();

printUrl($url);

$url = new Url('https://grigor:pass%40word@host.ru:8088/user/index?key1=value1&key2=value2#fragment');
$url->setSuffix('.php');
$url->updateSource();

printUrl($url);

function printUrl($url)
{
    echo '<br><br><b>Authority:</b> ', $url->getAuthority();
    echo '<br><b>BaseUrl:</b> ', $url->getBaseUrl();
    echo '<br><b>RelativeUrl:</b> ', $url->getRelativeUrl();
    echo '<br><b>AbsoluteUrl:</b> ', $url; //$url->getSource();
    echo '<br>';

    echo '<br><b>getScheme:</b> ', $url->getScheme();
    echo '<br><b>getUser:</b> ', $url->getUser();
    echo '<br><b>getPassword:</b> ', $url->getPassword();
    echo '<br><b>getHost:</b> ', $url->getHost();
    echo '<br><b>getPort:</b> ', $url->getPort();
    echo '<br><b>getPath:</b> ', $url->getPath();
    echo '<br><b>getSuffix:</b> ', $url->getSuffix();
    echo '<br><b>getQuery:</b> ', $url->getQuery();
    echo '<br><b>getFragment:</b> ', $url->getFragment();
}
```

Класс `\Compass\Url` может быть создан путем вызова статического метода `createBlank()` либо созданием нового
объекта оператором `new`. Метод `createBlank` примечателен тем, что в своей работе применяет клонирование своего же
прототипа. У данного метода есть два необязательных параметра: `$isIdnToAscii`, `$updateStrategy` - они определяют,
будет ли происходить преобразование хоста в `punycode`, и какую стратегию обновления/создания URL применять.

Первый параметр, `$isIdnToAscii` - определяет необходимость преобразования хоста при создании URL. Если он принимает
значение `true`, хост преобразуется, если же `false` - преобразование хоста не происходит. Этот параметр можно изменить
после создания экземпляра класса через метод `setConversionIdnToAscii()` - ему нужно передать новое значение.

Второй параметр, `$updateStrategy` - определяет стратегию обновления URL или его создания. Он включает в себя выбор
метода создания URL и его составляющих.

Чтобы создать новый экземпляр класса `\Compass\Url` с помощью оператора `new` необходимо передать один
обязательный параметр - это исходный URL в виде строки.

После настройки всех параметров для внесения изменений необходимо вызвать метод `updateSource();`.


---

## Компонент PATH

### Быстрый старт

```php 
<?php

use Compass\Path;

require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

$path = new Path('/__NAME__/__NAME__Scanner/__NAME2__Driver');

$path->replaceAll([
    '__NAME__' => 'User',
    '__NAME2__' => 'Filesystem',
]);
$path->updateSource();
echo '<br>', $path;
$path->setAll(['path','to','file.txt']);

$path->updateSource();
echo '<br>', $path;
```

Результат вывода:

``` 
/User/UserScanner/FilesystemDriver
path/to/file.txt
```

---

## Тестировать

``` php composer tests ```
