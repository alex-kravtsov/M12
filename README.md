# Копирование файлов с сервера разработчиков на бизнес-серверы с авторезервированием

## Исходные данные

### Имеются следующие серверы:

- Сервер разработчиков - отсюда будем брать файлы для
копирования

- Бета сервер

- Бизнес-серверы

Для каждого сервера известно следующее:

- Имя сервера (IP-адрес), имя пользователя для доступа через
SSH. Доступ к серверам осуществляется по публичному ключу

- Путь к каталогу проекта

Для бизнес-сервера, на котором будет выполняться резервное
копирование, кроме того, известно:

- Путь к файлу резервной копии

###  Исходные данные для управляющего процесса:

- Бета релиз: Список файлов для копирования (путь прописан относительно
каталога проекта)

- Production-релиз: ID релиза

## Общие положения

Скрипт предназначен для выполнения двух независимых задач:

- Копирование файлов с сервера разработчиков на бета сервер (бета-релиз)

При этом выполняется копирование файлов (каталогов) из списка и
сохранение сведений о релизе (дата релиза, файлы (каталоги)
релиза).

- Копирование файлов бета-релиза с бета сервера на бизнес-серверы

В первую очередь, производится резервное копирование выбранного
бизнес-сервера, затем копирование файлов релиза с бета-сервера на
каждый бизнес-сервер.

## Общий порядок копирования файлов на бета-сервер (бета-релиз)

1. Управляющий процесс получает список файлов, необходимых для
копирования. Пути к файлам прописываются относительно каталога
проекта

2. Управляющий процесс создаёт релиз и включает файлы из списка в
этот релиз. Если пути к файлу (каталогу) нет в базе данных, то
управляющий процесс записывает такой путь в базу данных и
включает в релиз

3. Управляющий процесс переводит релиз в состояние
"бета-копирование"

4. Рабочий процесс 1 выполняет копирование файлов (каталогов
релиза) с сервера разработчиков на бета-сервер

5. При возникновении ошибки Рабочий процесс 1 записывает ошибку в
журнал бета-копирования и переводит релиз в состояние "ошибка
бета-копирования"

6. При успешном завершении бета-копирования Рабочий процесс 1
переводит релиз в состояние "бета-копирование завершено"

*Управляющий процесс сохраняет порядок изменений т.е. не
может создать новый релиз пока не завершён предыдущий*

## Общий порядок копирования файлов бета-релиза на бизнес-серверы (процесс 1)

1. Управляющий процесс переводит текущий (незавершённый)
релиз в состояние "production-релиз"

2. Рабочий процесс 1 переводит релиз в состояние
"production-резервирование"

3. Рабочий процесс 1 запускает резервирование выбранного
бизнес-сервера

4. Рабочий процесс 1 переводит релиз в состояние
"production-копирование" и переводит бизнес-серверы в состояние
"копирование"

5. Рабочий процесс 1 опрашивает состояние бизнес-серверов, и
переводит релиз в состояние "завершено" после завершения
копирования на каждом из серверов.

6. В случае ошибки Рабочий процесс 1 переводит релиз в состояние
"production-ошибка" и записывает ошибку в журнал
production-копирования

## Общий порядок копирования файлов релиза на бизнес-серверы (процесс 2)

1. Рабочий процесс 2 (их может быть несколько, по числу
бизнес-серверов) переводит любой из бизнес-серверов, находящихся
в состоянии "копирование" в состояние "блокировка"

2. Рабочий процесс 2 копирует файлы релиза на бизнес-сервер

3. Рабочий процесс 2 переводит бизнес-сервер в исходное состояние
(снимает флаги "копирование" и "блокировка")

4. В случае ошибки Рабочий процесс 2 переводит бизнес-сервер в
состояние "ошибка" и делает запись об ошибке в журнал
бизнес-серверов
