<?php
 
// класс хранилища данных, реализующий интерфейс Iterator
class classHTML implements Iterator
{
    public $html=''; // файл html
	public $tegerror=''; //сборщик ошибок
    // массив, в котором хранятся метатеги
    private $options = [];
    // возвращает текущий элемент
    public function current()
    {
      return current($this -> options);
    }
    // возвращает ключ текущего элемента
    public function key()
    {
      return key($this -> options);
    }
    // передвигаемся вперед на один элемент
    public function next()
    {
      next($this -> options);
    }
    // возвращает указатель на начало массива фактически мы начинаем считать заново с нуля
    public function rewind()
    {
      reset($this -> options);
    }
    // проверяет, достигли ли мы конца массива
    public function valid()
    {
      return current($this -> options) !== false;
    }
    // метод для добавления мета тегов и их регулярного выражения в хранилище
    public function set($option, $value)
    {
      preg_match_all($value,$this->html, $htmlmatch);
      //проверка существования тега
      if(!empty($htmlmatch[0])){
            $this -> options[$option] = $htmlmatch[0][0];
      }else{
          $this -> tegerror .= 'Метатег '.$option.' не найден. '. PHP_EOL;
          $this -> options[$option] = $value;
      }
	  // если под одно регулярное выражение попадает более 1 результата - выведем ошибку. Согласно заданию - нужно вырезать только description, keywords и title, а они одиночны на странице
	  if(!empty($htmlmatch[0][1])){
		  $this -> tegerror .= 'Уточните регулярное выражение метатега '.$option. ' . Обработка более 1 совпадения регулярного выражения не реализована'. PHP_EOL;
	  }
        return $this;
    }
    // метод для получения метатега из хранилища
    public function get($option)
    {       return $this -> options[$option];
    }
}
 
 // создали объект
 $reg = new classHTML();
 
 //открываем файл html 
$clearHtml = file_get_contents('htmlbefore.html', true);
// Заносим его в объект, с созданием ссылки, чтобы происходила синхронизация вырезанных строк. И, на сколько я понял, в этом случае не создается новый массив в памяти.
$reg -> html =& $clearHtml; // благодаря этой ссылке, удаление происходит всех тегов, независимо от количества проходов в html. Насколько это правильно?
 // добавили список метатегов в объект
 $reg -> set("description",'#<meta name="description" .*>#mui')
    -> set("keywords", '#<meta name="keywords".*>#mui')
    -> set("title",'#<title>.*<\/title>#mui');
 
 // и прошлись по строкам.
 foreach ( $reg as $key => $value ) {
                $clearHtml = str_replace($value, "", $reg -> html);
 }
echo $clearHtml;
file_put_contents('clearhtml.html',$clearHtml); //сохраняем очищенный html файл
file_put_contents('error.log',$reg -> tegerror); // сохраняем лог ошибки
?>