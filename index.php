<?php
// 引用sphinxapi类
require "sphinxapi.php";
//关闭错误提示
error_reporting(E_ALL & ~E_NOTICE);
$num = 0;
if (!empty($_GET) && !empty($_GET['q'])) {
    $Keywords = strip_tags(trim($_GET['q']));
    if (!empty($_GET['m']) && 1 == $_GET['m']) {
        $Keywords = substr(md5($Keywords), 8, 16);
    }
    if (!empty($_GET['m']) && 2 == $_GET['m']) {
        $Keywords = md5($Keywords);
    }
    $cl = new SphinxClient();
    // 返回结果设置
    $cl->SetServer('192.168.25.129', 9312);
    $cl->SetConnectTimeout(3);
    $cl->SetArrayResult(true);
    // 设置是否全文匹配
    if (!empty($_GET) && !empty($_GET['f'])) {
        $cl->SetMatchMode(SPH_MATCH_ANY);
    } 
    if (!empty($_GET) && !empty($_GET['p'])) {
        $p = !intval(trim($_GET['p'])) == 0 ? intval(trim($_GET['p'])) - 1 : 0;
        $p = $p * 20;
        // 我在sed.conf 设置了最大返回结果数1000。但是我在生成页码的时候最多生成20页，我想能满足大部分搜索需求了。
        // 以下语句表示从P参数偏移开始每次返回20条。
        $cl->setLimits($p, 20);
    } else {
        $cl->setLimits(0, 20);
    }
    $res = $cl->Query("$Keywords", "*");
    //var_dump($res);
        @mysql_connect("localhost", "test", "mima"); //数据库账号密码
    mysql_select_db("test"); //数据库库名名
    mysql_query("set names utf8");
    $tables = ['spdb1' ,'spdb2','spdb3','spdb4','spdb5'];  //把表名放入数组
    function getResult($id, $table)
    {
            $sql    = "select username,email,password,salt,site from {$table} where id = " . $id;
            $result = mysql_query($sql);
            while ($row = mysql_fetch_array($result)) {
                echo "<tr><td>" . $row['username'] . "</td>";
                echo "<td>" . $row['email'] . "</td>";
                echo "<td>" . $row['password'] . "</td>";
                echo "<td>" . $row['salt'] . "</td>";
                echo "<td>" . $row['site'] . "</td></tr>";
            }
    }
    if ($res["total_found"]) {
        $num = $res["total_found"];
    } else {
        $num = 0;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
   <title>The Web of Answers</title>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-with,initial-scal=1">
   <link href="bootstrap.min.css" rel="stylesheet">
   <script>
    function check(form){
        if(form.q.value.length<4){
          alert("关键字长度请大于4！");
          form.q.focus();
          return false;
        }
    }
    </script>
   <style>
        h1 {
            font-family: Times New Roman, Lucida Handwriting;
        }
   </style>
</head>
<body>
    <div class="container" id="container">
        <div id="page-header">
            <h1 class="text-center"> 超级社工库查询系统</h1>
        </div>
        <div class="row">
        <form action="" method="get" class="form-horizontal" role="form">
         
            <div class="input-group col-md-6 col-md-offset-3">
                <input type="text" class="form-control" name="q" placeholder="请输入" value="<?php echo strip_tags(trim($_GET['q']));?>">
                    <div class="input-group-btn">
                        <button type="submit" class="btn btn-primary" onClick="check(form)">Search</button>
                    </div>
             </div>
        </form>
    </div>
    <br>
<?php
if (0 != $num) {
    echo "<div class=\"row\">
    <div class=\"alert alert-success alert-dismissible col-md-10 col-md-offset-1\" role=\"alert\">
    <button type=\"button\" class=\"close\" data-dismiss=\"alert\"><span aria-hidden=\"true\">&times;</span><span class=\"sr-only\">Close</span></button>
    找到与<b>&nbsp{$Keywords}&nbsp</b>相关的结果 {$num} 个。用时 {$res['time']} 秒。</div>";
    echo "<div class=\"table-responsive col-md-10 col-md-offset-1\">
        <table class=\"table table-striped table-hover\">
          <tr>
          <th>Username</th>
          <th>Email</th>
          <th>Password</th>
          <th>Salt</th>
          <th>From</th>
          </tr>";
    if (is_array($res["matches"])) {
        foreach ($res["matches"] as $docinfo) {
            $table_id = $docinfo['attrs']['table_id'];
            getResult($docinfo['id'], $tables[$table_id - 1]);
            }
    }
    echo "</table></div></div>";
    } else {
        if (!empty($_GET) && !empty($_GET['q'])) {
            echo "<div class=\"alert alert-warning alert-dismissible col-md-10 col-md-offset-1\" role=\"alert\">
                <button type=\"button\" class=\"close\" data-dismiss=\"alert\"><span aria-hidden=\"true\">&times;</span><span class=\"sr-only\">Close</span></button>
                找不到与<b>&nbsp{$Keywords}&nbsp</b>相关的结果。请更换其他关键词试试。</div></div>";
        }
}
?>
    <div id="pages">
    <center>
        <nav>
            <ul class="pagination">
<?php
if ($num !== 0) {
    $pagecount = (int) ($num / 20);
    if (!($num % 20) == 0) {
        $pagecount = $pagecount + 1;
    }
    if ($pagecount > 20) {
        $pagecount = 20;
    }
    $highlightid = !intval(trim($_GET['p'])) == 0 ? intval(trim($_GET['p'])) : 1;
    for ($i = 1; $i <= $pagecount; $i++) {
        if ($highlightid == $i) {
            echo "<li class=\"active\"><a href=\"#\">{$i}<span class=\"sr-only\">(current)</span></a></li>";
        } else {
            echo "<li><a href=\"index.php?q={$Keywords}&p={$i}\">{$i}</a></li>";
        }
    }
}
?>
            </ul>
        </nav>
    </center>
    </div>
    <div id="footer">
        <p class="text-center">
        The Web of Answers &copy;2010-2015 | <a href="http://www.findmima.com">Findmima.com</a></p>
    </div>
    </div>
</body>
</html>