## Модуль "Турнирные таблицы"

Модуль реализует функциональность «Турнирная сетка», а так же административный интерфейс по управлению ею.

Турнирная сетка имеет тип «на выбывание». Она строится на основе участия 2-х команд, 
из которых один победитель выходит в следующий тур.

Компонент в публичном интерфейсе строит схему турнира.
В административном интерфейсе существует возможность управлять этой сеткой.
Там же есть возможность просмотра отчета по командам (кнопка "Отчет" в карточке турнирной таблицы)
 с полями:
  * «Команда», 
  * «Место в турнире», 
  * «Общая результативность за турнир», 
  * «Средняя результативность за игру», 
  * «Лучшая результативность за игру». 

При установке модуль автоматически заливает демо данные и создает раздел /standings/ в публичке.

#### Текущая версия модуля имеет следующие ограничения:
- JS код написан с использованием фичей ES6 поэтому вероятно редактирование турнирной таблицы не будет работать в старых браузерах
- Используется BX.Vue, поэтому необходимо наличие модуля ui версии не менее 18.5.1 
- Не реализован CRUD дял справочника команд
- Не отслеживается удаление команы, привязанной к какой-либо турнирной сетке
- Не реализована настройка прав и управление дсотупом
- Список турнирных таблиц не поддерживает пейджинг и сортировку
- Не реализована возможность удаления турнирной таблицы
- Не реализована проверка на некорректные данные при сохранении турнирной таблицы
- Список в отчете не поддерживает сортировку
- Не все тексты вынесены в языковые файлы
- Нет публичного компонента со списком турнирных таблиц
- Публичный компонент, отображающей турнирную таблицу не поддерживает в визуальном режиме
- Подглючивает галка "Игра за 3 место между проигравшими в 1/2 финала" 
 