<?php

include '/var/www/html/class/chdocument.php';

class documentTest extends chdocument {
	public function testEliminate() {
		$this -> document = '<ul>
	<li class="item">test1</li>

	<li class="item">test2</li>
	<!--<ul><li>commenttest</li><li>commenttest</li></ul>-->
	<li class="item">
		<ul>
			<li class="item">test3-1</li>
			<li class="item">test3-2</li>
		</ul>
	</li>
	<script>var test = "<ul><li>scripttest</li><li>scripttest</li></ul>";</script>
	<li class="item">test4
	<li class="item">test5
	<li class="item">
		<ul>
			<li class="item">test6-1
			<li class="item">test6-2
		</ul>
</ul>

<p>test</p>';

		$this -> self = $this;
		$this -> length = strlen($this -> document);
		$a = array(
			69 => '<!--<ul><li>commenttest</li><li>commenttest</li></ul>-->',
			185 => '<script>var test = "<ul><li>scripttest</li><li>scripttest</li></ul>";</script>'
			);
		$this -> self -> exclusion = $this -> eliminate();
		$this -> assertEquals($a, $this -> self -> exclusion);

		$document = '<ul>
	<li class="item">test1</li>

	<li class="item">test2</li>
	
	<li class="item">
		<ul>
			<li class="item">test3-1</li>
			<li class="item">test3-2</li>
		</ul>
	</li>
	
	<li class="item">test4
	<li class="item">test5
	<li class="item">
		<ul>
			<li class="item">test6-1
			<li class="item">test6-2
		</ul>
</ul>

<p>test</p>';


		$this -> assertEquals($document, $this -> document);

		$document = '<ul>
	<li class="item">test1</li>

	<li class="item">test2</li>
	<!--<ul><li>commenttest</li><li>commenttest</li></ul>-->
	<li class="item">
		<ul>
			<li class="item">test3-1</li>
			<li class="item">test3-2</li>
		</ul>
	</li>
	<script>var test = "<ul><li>scripttest</li><li>scripttest</li></ul>";</script>
	<li class="item">test4
	<li class="item">test5
	<li class="item">
		<ul>
			<li class="item">test6-1
			<li class="item">test6-2
		</ul>
</ul>

<p>test</p>';

		$this -> assertEquals($document, $this -> outerHTML);

	}

	public function testText() {
		$this -> document = '<a href="http://test.com">test<span>tag</span>test2</a>';
		$this -> length = strlen($this -> document);
		$a = $this -> text($this -> document, $this -> pos, $this -> length);
		$b = 'testtagtest2';
		$this -> assertEquals($a, $b);
	}

	public function testSplit() {
		$stiring = ' test test1  test2 test3 ';
		$a = $this -> split($stiring, ' ');
		$b = array(
			0 => 'test',
			1 => 'test1',
			2 => 'test2',
			3 => 'test3',
		);
		$this -> assertEquals($a, $b);
	}

	public function testGetElementByAttribute() {

		$this -> document = '<ul>
	<li class="item">test1</li>
	<li class="item">test2</li>
	<li class="item">
		<ul>
			<li class="item">test3-1</li>
			<li class="item">test3-2</li>
		</ul>
	</li>
	<li class="item">test4
	<li class="item">test5
	<li class="item">
		<ul>
			<li class="item">test6-1
			<li class="item">test6-2
		</ul>
</ul>

<p>test</p>';

		$this -> length = strlen($this -> document);

		$a = $this -> getElementByAttribute($this -> document, $this -> pos, $this -> length, 'item', 0, 'class');
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '<li class="item">test1</li>';
		$this -> assertEquals($a, $b);


		$a = $this -> getElementByAttribute($this -> document, $this -> pos, $this -> length, 'item', 1, 'class');
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '<li class="item">test2</li>';
		$this -> assertEquals($a, $b);

		$a = $this -> getElementByAttribute($this -> document, $this -> pos, $this -> length, 'item', 2, 'class');
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '<li class="item">
		<ul>
			<li class="item">test3-1</li>
			<li class="item">test3-2</li>
		</ul>
	</li>';
		$this -> assertEquals($a, $b);

		$a = $this -> getElementByAttribute($this -> document, $this -> pos, $this -> length, 'item', 3, 'class');
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '<li class="item">test3-1</li>';
		$this -> assertEquals($a, $b);

		$a = $this -> getElementByAttribute($this -> document, $this -> pos, $this -> length, 'item', 4, 'class');
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '<li class="item">test3-2</li>';
		$this -> assertEquals($a, $b);

		$a = $this -> getElementByAttribute($this -> document, $this -> pos, $this -> length, 'item', 5, 'class');
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '<li class="item">test4
	';
		$this -> assertEquals($a, $b);

		$a = $this -> getElementByAttribute($this -> document, $this -> pos, $this -> length, 'item', 6, 'class');
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '<li class="item">test5
	';
		$this -> assertEquals($a, $b);

		$a = $this -> getElementByAttribute($this -> document, $this -> pos, $this -> length, 'item', 7, 'class');
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '<li class="item">
		<ul>
			<li class="item">test6-1
			<li class="item">test6-2
		</ul>
';
		$this -> assertEquals($a, $b);

		$a = $this -> getElementByAttribute($this -> document, $this -> pos, $this -> length, 'item', 8, 'class');
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '<li class="item">test6-1
			';
		$this -> assertEquals($a, $b);

		$a = $this -> getElementByAttribute($this -> document, $this -> pos, $this -> length, 'item', 9, 'class');
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '<li class="item">test6-2
		';
		$this -> assertEquals($a, $b);

		$a = $this -> getElementByAttribute($this -> document, $this -> pos, $this -> length, 'item', 10, 'class');
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '';
		$this -> assertEquals($a, $b);

	}

	public function testGetElementsByTag() {

		$this -> document = '<ul>
	<li>test1</li>
	<li>test2</li>
	<li>
		<ul>
			<li>test3-1</li>
			<li>test3-2</li>
		</ul>
	</li>
	<li>test4
	<li>test5
	<li>
		<ul>
			<li>test6-1
			<li>test6-2
		</ul>
</ul>

<p>test</p>';

		$this -> length = strlen($this -> document);


		$a = $this -> getElementsByTag($this -> document, $this -> pos, $this -> length, '<li', 0);
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '<li>test1</li>';
		$this -> assertEquals($a, $b);

		$a = $this -> getElementsByTag($this -> document, $this -> pos, $this -> length, '<li', 1);
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '<li>test2</li>';
		$this -> assertEquals($a, $b);

		$a = $this -> getElementsByTag($this -> document, $this -> pos, $this -> length, '<li', 2);
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '<li>
		<ul>
			<li>test3-1</li>
			<li>test3-2</li>
		</ul>
	</li>';
		$this -> assertEquals($a, $b);

		$a = $this -> getElementsByTag($this -> document, $this -> pos, $this -> length, '<li', 3);
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '<li>test3-1</li>';
		$this -> assertEquals($a, $b);

		$a = $this -> getElementsByTag($this -> document, $this -> pos, $this -> length, '<li', 4);
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '<li>test3-2</li>';
		$this -> assertEquals($a, $b);

		$a = $this -> getElementsByTag($this -> document, $this -> pos, $this -> length, '<li', 5);
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '<li>test4
	';
		$this -> assertEquals($a, $b);

		$a = $this -> getElementsByTag($this -> document, $this -> pos, $this -> length, '<li', 6);
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '<li>test5
	';
		$this -> assertEquals($a, $b);

		$a = $this -> getElementsByTag($this -> document, $this -> pos, $this -> length, '<li', 7);
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '<li>
		<ul>
			<li>test6-1
			<li>test6-2
		</ul>
';
		$this -> assertEquals($a, $b);

		$a = $this -> getElementsByTag($this -> document, $this -> pos, $this -> length, '<li', 8);
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '<li>test6-1
			';
		$this -> assertEquals($a, $b);

		$a = $this -> getElementsByTag($this -> document, $this -> pos, $this -> length, '<li', 9);
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '<li>test6-2
		';
		$this -> assertEquals($a, $b);

		$a = $this -> getElementsByTag($this -> document, $this -> pos, $this -> length, '<li', 10);
		$a = substr($this -> document, $a[0], $a[1]);
		$b = '';
		$this -> assertEquals($a, $b);

	}



	public function testSetAttribute() {
		$this -> document = '<ul>
	<li class="item">test1</li>

	<li class="item">test2</li>
	<!--<ul><li>commenttest</li><li>commenttest</li></ul>-->
	<li class="item">
		<ul>
			<li class="item">test3-1</li>
			<li class="item">test3-2</li>
		</ul>
	</li>
	<script>var test = "<ul><li>scripttest</li><li>scripttest</li></ul>";</script>
	<li class="item">test4
	<li class="item">test5
	<li class="item">
		<ul>
			<li class="item">test6-1
			<li class="item">test6-2
		</ul>
</ul>

<p>test</p>';


		$b = '<ul>
	<li              id="test" class="test">test1</li>

	<li class="item">test2</li>
	<!--<ul><li>commenttest</li><li>commenttest</li></ul>-->
	<li class="item">
		<ul>
			<li class="item">test3-1</li>
			<li class="item">test3-2</li>
		</ul>
	</li>
	<script>var test = "<ul><li>scripttest</li><li>scripttest</li></ul>";</script>
	<li class="item">test4
	<li class="item">test5
	<li class="item">
		<ul>
			<li class="item">test6-1
			<li class="item">test6-2
		</ul>
</ul>

<p>test</p>';

		$this -> self = $this;
		$this -> self -> exclusion = $this -> eliminate();
		$this -> length = strlen($this -> document);
		$c = $this -> getElementsByTag($this -> document, $this -> pos, $this -> length, '<li', 0);
		$this -> document = $this -> setAttribute($this -> document, $c[0], 'id', 'test');
		$this -> document = $this -> setAttribute($this -> document, $c[0], 'class', 'test');
		$this -> assertEquals($this -> outerHTML, $b);



		$document = new chdocument($this -> document);
		$a = '<li class="item" checked="checked">test6-1
			';
		$document -> getElementsByTagName('li') -> item(8) -> checked = true;
		$this -> assertEquals($a, $document -> getElementsByTagName('li') -> item(8) -> outerHTML);

		$a = '<li class="item" >test6-1
			';
		$document -> getElementsByTagName('li') -> item(8) -> checked = false;
		$this -> assertEquals($a, $document -> getElementsByTagName('li') -> item(8) -> outerHTML);



	}

	public function testOuterHTML() {
		$str = '<ul>
	<!--<ul><li>commenttest</li><li>commenttest</li></ul>-->

	<li class="item">
		<ul>
			<li class="item">test3-1</li>
			<li class="item">test3-2</li>
		</ul>
	</li>

	<script>var test = "<ul><li>scripttest</li><li>scripttest</li></ul>";</script>
	<li class="item">
		<ul>
			<li class="item">test6-1
			<li class="item">test6-2
		</ul>
</ul>

<p>test</p>';

		$document = new chdocument($str);
		$ul = $document -> getElementsByTagName('ul');
		$li = $ul -> item(0) -> getElementsByTagName('li');
		$li -> item(0) -> outerHTML = $li -> item(0) -> outerHTML. '<li>addtest</li>';
		$a = '<li>addtest</li>';
		$b = $li -> item(3) -> outerHTML;
		$this -> assertEquals($a, $b);

	}

	public function testInnerHTML() {
		$str = '<ul>
	<!--<ul><li>commenttest</li><li>commenttest</li></ul>-->

	<li class="item">
		<ul>
			<li class="item">test3-1</li>
			<li class="item">test3-2</li>
		</ul>
	</li>

	<script>var test = "<ul><li>scripttest</li><li>scripttest</li></ul>";</script>
	<li class="item">
		<ul>
			<li class="item">test6-1
			<li class="item">test6-2
		</ul>
</ul>

<p>test</p>';

		$document = new chdocument($str);
		$ul = $document -> getElementsByTagName('ul');
		$li = $ul -> item(0) -> getElementsByTagName('li');
		$ul -> item(0) -> innerHTML = $ul -> item(0) -> innerHTML. '<li>addtest</li>';
		$a = '<li>addtest</li>';
		$b = $li -> item(6) -> outerHTML;
		$this -> assertEquals($a, $b);

	}

	public function testParentNode() {
		$str = '<ul>
	<li class="item">test1</li>

	<li class="item">test2</li>
	<!--<ul><li>commenttest</li><li>commenttest</li></ul>-->
	<li class="item">
		<ul>
			<li class="item">test3-1</li>
			<li class="item">test3-2</li>
		</ul>
	</li>
	<script>var test = "<ul><li>scripttest</li><li>scripttest</li></ul>";</script>
	<li class="item">test4
	<li class="item">test5
	<li class="item">
		<ul>
			<li class="item">test6-1
			<li class="item">test6-2
		</ul>
</ul>

<p>test</p>';
		$document = new chdocument($str);

		$a = '<ul>
			<li class="item">test6-1
			<li class="item">test6-2
		</ul>';
		$b = $document -> getElementsByTagName('li') -> item(8) -> parentNode -> outerHTML;
		$this -> assertEquals($a, $b);

	}

	public function testChildren() {
		$str = '<ul>
	<li class="item">test1</li>

	<li class="item">test2</li>
	<!--<ul><li>commenttest</li><li>commenttest</li></ul>-->
	<li class="item">
		<ul>
			<li class="item">test3-1</li>
			<li class="item">test3-2</li>
		</ul>
	</li>
	<script>var test = "<ul><li>scripttest</li><li>scripttest</li></ul>";</script>
	<li class="item">test4
	<li class="item">test5
	<li class="item">
		<ul>
			<li class="item">test6-1
			<li class="item">test6-2
		</ul>
</ul>

<p>test</p>';
		$document = new chdocument($str);
		$b = $document -> getElementsByTagName('ul') -> item(0) -> children;
		$a = '<li class="item">
		<ul>
			<li class="item">test6-1
			<li class="item">test6-2
		</ul>
';

		$b = $b[5] -> outerHTML;
		$this -> assertEquals($a, $b);
	}
}