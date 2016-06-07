<?php
require 'Kint.class.php';


//Kint::dump( $_SERVER );
// 或许，可以试试更简单的，使用缩写:
//d( $_SERVER );
// 或者，你可以用dd()在打印完之后直接结束程序;
//dd( $_SERVER ); // 相当于 d( $_SERVER ); die;


// 跟踪调试信息:
Kint::trace();
// 这么写跟上面的效果相同
//Kint::dump( 1 );


// 禁用所有输出
//Kint::enabled(false);
// 之后这样的方法调用，都不会有输出了
d('Get off my lawn!'); // 没有效果