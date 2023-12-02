[![codecov](https://codecov.io/gh/vinogradsoft/compass/graph/badge.svg?token=S1XRZ1GEY8)](https://codecov.io/gh/vinogradsoft/compass)
# Compass

> Compass - это библиотека, предназначенная для работы с URL-адресами и путями к файлам на жестком диске. В ее состав
> входят инструменты для упрощения манипуляций с этими данными. Цель библиотеки - облегчить работу с URL-адресами и
> данными о расположении файлов.

## Общая информация

Compass может быть использован в различных PHP-приложениях для обработки путей к файлам и URL-адресов. Он включает в
себя два основных компонента: `Compass\Path` и `Compass\Url`. Оба этих компонента представляют собой отдельные объекты
и предлагают набор методов для простого манипулирования данными. `Compass\Path` предоставляет инструменты для работы с
директориями, включая поиск, замену и проверку наличия директорий в строке пути, а также изменение их порядка. В свою
очередь, `Compass\Url` обеспечивает возможности для работы с URL, позволяя создавать, получать и изменять различные
части URL-адреса.

Оба компонента работают по схожему принципу, сначала собирают необходимые параметры, а затем применяют их к результату с
помощью специального метода `updateSource()`.

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

$url->setScheme('http')->setUser('grigor')->setPassword('password')->setHost('vinograd.soft')
    ->setPort('8080')->setPath('/path/to/resource')->setSuffix('.json')
    ->setArrayQuery(['query' => 'value',])->setFragment('fragment');

$url->updateSource();

echo '<br><br><b>Authority:</b> ', $url->getAuthority();
echo '<br><b>Base Url:</b> ', $url->getBaseUrl();
echo '<br><b>Relative Url:</b> ', $url->getRelativeUrl();
echo '<br><b>Absolute Url:</b> ', $url->getSource(), '<br>';
echo '<br><b>Scheme:</b> ', $url->getScheme();
echo '<br><b>User:</b> ', $url->getUser();
echo '<br><b>Password:</b> ', $url->getPassword();
echo '<br><b>Host:</b> ', $url->getHost();
echo '<br><b>Port:</b> ', $url->getPort();
echo '<br><b>Path:</b> ', $url->getPath();
echo '<br><b>Suffix:</b> ', $url->getSuffix();
echo '<br><b>Query:</b> ', $url->getQuery();
echo '<br><b>Fragment:</b> ', $url->getFragment();

$url->setSource('http://россия.рф');
$url->setConversionIdnToAscii(true)->updateSource();
echo '<br><br><b>new URL:</b> ',$url; #http://xn--h1alffa9f.xn--p1ai
```

### Создание экземпляра класса Compass\Url

Объект класса `Compass\Url` может быть создан путем вызова статического метода `createBlank()`, либо с помощью
оператора `new`. Метод `createBlank` примечателен тем, что в своей работе применяет клонирование своего же
прототипа. У данного метода есть два необязательных параметра: `$isIdnToAscii`, `$updateStrategy` - они определяют,
будет ли происходить преобразование хоста в `punycode`, и какую стратегию обновления URL применять.

Первый параметр, `$isIdnToAscii` - определяет необходимость преобразования хоста. Если он принимает значение `true`,
хост преобразуется, если же `false` - преобразование хоста не происходит.

Второй параметр, `$updateStrategy` - определяет стратегию обновления URL. Стратегия включает в себя методы создания
составляющих URL-адреса.

Чтобы создать новый экземпляр класса `Compass\Url` с помощью оператора `new` необходимо передать один
обязательный параметр - это исходный URL в виде строки.

### Варианты сборки URL

Параметры можно устанавливать с помощью:

- конструктора
- метода `$url->setSource(string $src);`
- метода `$url->setAll(array $parts);`
- методов отвечающих за конкретную часть Url (как показано в быстром старте).

### Примеры

#### **Через конструктор**

```php
$url = new Url('http://grigor:password@vinograd.soft:8080/path/to/resource?query=value#fragment');
```

#### **С помощью метода `$url->setSource(string $src);`**

```php
$url->setSource('http://grigor:password@vinograd.soft:8080/path/to/resource?query=value#fragment');
```

#### **С помощью метода `$url->setAll(array $parts);`**

```php
$url->setAll([
    ':host' => 'host.ru',
    ':scheme' => 'http',
    ':user' => 'user',
    ':password' => 'password',
    ':port' => '80',
    ':path' => ['path', 'to', 'resource'],
    '?' => ['key' => 'value', 'key2' => 'value2'],
    '#' => 'fragment',
    ':suffix' => '.json',
]);
```

#### **С помощью методов отвечающих за конкретную часть Url.**

```php
$url->setScheme('http')->setUser('grigor')->setPassword('password')->setHost('vinograd.soft')
    ->setPort('8080')->setPath('/path/to/resource')->setSuffix('.json')
    ->setArrayQuery(['query' => 'value',])->setFragment('fragment');
```

В первых двух вариантах суффикс не распознается. В таких случаях суффикс необходимо установить отдельным методом
`$url->setSuffix(.json);`, только так вы сможете им управлять.

> Суффикс не анализируется, так как он может быть любой строкой, и не обязательно начинаться с точки. Если вы передаете
> URL с суффиксом, то он становится частью path.

### Применение изменений

Чтобы измененные параметры вступили в силу, необходимо вызвать метод `$url->updateSource()`. Этот метод имеет два
необязательных аргумента - `$updateAbsoluteUrl` и `$suffix`. Значение аргумента `$updateAbsoluteUrl` определяет, будет
ли обновляться весь URL-адрес или только его относительная часть. По умолчанию его значение равно `true`, другими
словами, по умолчанию будет пытаться обновить весь URL. Параметр `$suffix` имеет тип `string` и позволяет установить
суффикс в моменте обновления.

После того как параметры были применены, вы можете получить обновленный результат, методом `$url->getSource()`.

### Стратегии обновления

> ***Стратегия обновления*** - это объект, который объединяет все входные данные для создания итогового URL-адреса.
> Этот объект должен быть реализацией интерфейса `Compass\UrlStrategy`. В системе, класс, который выполняет эту функцию,
> называется `Compass\DefaultUrlStrategy`. <br>

Стратегия придумана, для того, чтобы контролировать процесс создания URL. Она содержит набор методов по одному
на каждый участок URL-адреса, в которых происходит склеивание параметров. Какие методы будут участвовать в
обновлении определяется в объекте `Compass\Url` на основе состояний участков. Есть некие флаги которые говорят какие
участки нужно пересоздать. Для простоты восприятия можно провести аналогию с дорогой поделенной на какое-то количество
участков "A", "B", "C", "D" и дорожной компанией которая за нее ответственна. В идеале дорога должна быть всегда ровная.
Когда на дороге повреждается какой-нибудь участок, например "С", ремонтная бригада выезжает на этот участок и
ремонтирует его. Так же можно представить и URL-адрес, где дорога это строка у которой есть логические части - участки.
Ремонтные бригады - это методы в стратегии обновления. Дорожная компания - это объект `Compass\Url`.

У стратегии есть шесть методов в которых создаются части URL-адреса:

- `updateAuthority()`
- `updateBaseUrl()`
- `updateQuery()`
- `updatePath()`
- `updateRelativeUrl()`
- `updateAbsoluteUrl()`.

Устанавливая какой-либо параметр для URL, система меняет состояние участка, как бы, повреждает тот
участок для которого был передан параметр. После вызова метода `$url->updateSource();` в работу включаются
соответствующие методы стратегии.

> Важно запомнить, что в объекте `Compass\Url` хранятся исходные части, которые передал пользователь и результаты работы
> каждого метода стратегии.

Выполнение методов можно поделить условно на три уровня.

```
                                             УРОВЕНЬ 3
        |--------------------------------updateAbsoluteUrl()---------------------------------|
        |                                                                                    |
        |                                    УРОВЕНЬ 2                                       |
        |---------------updateBaseUrl()-----------|------------updateRelativeUrl()-----------|
        |                                         |                                          |
        |                                    УРОВЕНЬ 1                                       |
        |                updateAuthority()        |     updatePath()     updateQuery()       |
        |       /‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾\|/‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾\ /‾‾‾‾‾‾‾‾\         |
        |http://grigor:password@vinograd.soft:8080/path/to/resource.json?query=value#fragment|
```

Метод третьего уровня `updateAbsoluteUrl()` выполняется всегда, в нем соединяются сохраненные результаты работы методов
второго уровня. Методы второго уровня `updateBaseUrl()` и `updateRelativeUrl()` соединяют результаты работы первого
уровня. На первом уровне `updateAuthority()`, `updatePath()` и `updateQuery()` склеивают исходные части каждый в своей
области.

### Манипуляция состояниями

Состояния хранятся в нескольких полях класса `Compass\Url`. `$authoritySate`, `$relativeUrlState` и `$schemeState`.
`$authoritySate` и `$relativeUrlState` имеют тип `int`, `$schemeState` - `bool`. Управление состояниями `$authoritySate`
и `$relativeUrlState` осуществляются битовыми операциями. Вот пример кода, который показывает их значения по умолчанию
когда состояние неповрежденное:

```php
const USER_STATE = 1 << 0;
const PASSWORD_STATE = 1 << 1;
const HOST_STATE = 1 << 2;
const PORT_STATE = 1 << 3;

const PATH_STATE = 1 << 0;
const QUERY_STATE = 1 << 1;
const FRAGMENT_STATE = 1 << 2;

const AUTHORITY_WHOLE = self::USER_STATE | self::PASSWORD_STATE | self::HOST_STATE | self::PORT_STATE;
const RELATIVE_URL_WHOLE = self::PATH_STATE | self::QUERY_STATE | self::FRAGMENT_STATE;

/**
 * current states
 */
protected int $authoritySate = self::AUTHORITY_WHOLE;
protected int $relativeUrlState = self::RELATIVE_URL_WHOLE;
protected bool $schemeState = true;
```

Если мы хотим повредить состояние `$relativeUrlState` в области `query`, то мы можем воспользоваться побитовыми
операторами:

```php
$relativeUrlState &= ~Url::QUERY_STATE;
```

Остальными участками можно манипулировать аналогичным способом, исключением является только `$schemeState` ему нужно
присвоить булево значение.

### Пример создания своей стратегии

При построении своего процесса обновления URL, иногда требуется, чтобы выполнился метод который на основе текущего
состояния не выполниться. В таких случаях используется дополнительный метод `forceUnlockMethod(...)` в котором можно
изменить текущее состояние определенного участка, тем самым заставить систему выполнить нужный метод.

Это лучше понять на примере. Представим, что нам нужно генерировать URL для реферальных ссылок. Создадим стратегию в
которой будет добавляться для всех URL с доменом `another.site` параметр `refid` равный `40`.

Код стратегии:

```php
<?php

namespace <your\namespace>;

use Compass\DefaultUrlStrategy;
use Compass\Url;

class ReferralUrlStrategy extends DefaultUrlStrategy
{

    private bool $isAllowInsertParam = false;

    /**
     * @inheritDoc
     */
    public function updateQuery(array $items): string
    {
        if ($this->isAllowInsertParam) {
            $items['refid'] = 40;
            $this->isAllowInsertParam = false;
        }
        return http_build_query($items, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * @inheritDoc
     */
    public function forceUnlockMethod(
        bool    &$schemeState,
        int     &$authoritySate,
        int     &$relativeUrlState,
        array   $items,
        array   $pathItems,
        array   $queryItems,
        bool    $updateAbsoluteUrl,
        ?string $suffix = null
    ): void
    {
        if ($items[Url::HOST] === 'another.site') {
            $this->isAllowInsertParam = true;
        }
        $relativeUrlState &= ~Url::QUERY_STATE;
    }

}
```

Теперь установим стратегию и выведем два URL, один из которых будет целевым, с доменом `another.site`:

```php
$url = Url::createBlank();
$url->setUpdateStrategy(new ReferralUrlStrategy());

$url->setSource('https://another.site');
$url->updateSource();
echo $url->getSource(), '<br>'; # https://another.site/?refid=40

$url->setHost('vinograd.soft');
$url->updateSource();
echo $url->getSource(); # https://vinograd.soft
```

На первый взгляд может показаться, что изменение состояния (`$relativeUrlState &= ~Url::QUERY_STATE;`) нужно было
написать внутри `if` конструкции, но это не так. После того как мы установили хост `vinograd.soft`, вызов
метода `updateSource()` не привел бы к выполнению метода `updateQuery` нашей стратегии, поскольку не было добавлено ни
одного параметра штатным способом, который мог бы изменить состояние этого участка. В результате состояние осталось бы
не поврежденным, обновился бы только участок `baseurl` и сохраненный параметр `refid=40` с прошлого раза, был бы склеен
с новым `baseurl` который содержит хост `vinograd.soft`.

Этот пример показывает, что вы не ограничены только штатными методами установки частей URL. Используя стратегии можно
делать пост обработку результата, например когда нужно экранировать результат для HTML-атрибутов содержащих URL (href,
src и другие атрибуты этого типа).

---

## Компонент PATH

`Compass\Path` можно охарактеризовать как объектное представление пути к файлу. Он оперирует строкой пути не
опираясь на реальную файловую систему. Компонент так же как и `Compass\Url` имеет стратегию обновления, которая включает
в себя один метод `updatePath()`. Важно отметить, что этот компонент не имеет состояний.

### Демонстрация методов

```php
<?php

use Compass\Path;

require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

$path = new Path('/__NAME__/__NAME__Scanner/__NAME2__Driver', '/');

$path->replaceAll([
    '__NAME__' => 'User',
    '__NAME2__' => 'Filesystem',
]);
$path->updateSource();

echo '<br>', $path; # /User/UserScanner/FilesystemDriver

$path->setAll(['path', 'to', 'file.txt']);
$path->updateSource();

echo '<br>', $path; # path/to/file.txt

$path->set(1, 'newTo');
$path->updateSource();

echo '<br>', $path; # path/newTo/file.txt

$path->setBy('path', 'newPath');
$path->updateSource();

echo '<br>', $path; # newPath/newTo/file.txt

echo '<br>', $path->dirname(); # newPath/newTo

$path->setSuffix('.v');
$path->updateSource();
echo '<br>', $path; # newPath/newTo/file.txt.v

$path->setSource('newPath/newTo/file');
$path->setSuffix('.v');
$path->updateSource();
echo '<br>', $path; # newPath/newTo/file.v

$path->replace('newPath','path');
$path->updateSource();
echo '<br>', $path; # path/newTo/file.v
echo '<br>', $path->getLast(); # file.v
```

## Тестировать

``` php composer tests ```


### Содействие
Пожалуйста, смотрите [ВКЛАД](CONTRIBUTING.md) для получения подробной информации.

### Лицензия
Лицензия MIT (MIT). Пожалуйста, смотрите [файл лицензии](LICENSE) для получения дополнительной информации.