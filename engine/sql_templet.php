<?php
class SQL_TEMPLET {
    /**
     * 将sql转为tpl
     * 1.将所有存字符串转换为符号 ?
     * 2.将所有存数字转换为符号 ? 
     * 3.将in后面的括号内的内容替换为 ?, 子查询不会被替换
     * 4.将包含有数字的字符串(表名，列名)的数字部分替换为xxx
     */
    public static function transferSQLToTpl($sql){
        $sqlTpl = '';
        //替换纯字符串
        $pattern = ';
            ("|\')
            (?:
                \\\\\\\\        # 优先处理双斜线转义
                |
                \\\\"           # 处理双引号转义
                |
                \\\\\'          # 处理单引号转义
                |
                \\\\            # 优先处理单斜线转义
                |
                [^\'"\\\\]*+    # 占有优先匹配所有非转义及引号字符
            )*
            \g{1}
            ;xSi';
        $sqlTpl = preg_replace($pattern, "?", $sql);

        //替换注释为空,多行匹配
        $pattern = ';(\\\\)?\/\*.*?\*(\\\\)?\/;s';
        $sqlTpl = preg_replace($pattern, "", $sqlTpl);

        //替换纯数字, 
        $pattern = ';-?\b\d+\b(?:\.\d+\b)?;';
        $sqlTpl = preg_replace($pattern, "?", $sqlTpl);

        //替换in语句(因为第一步已经将字符串全部替换为问号，此时不用考虑字符串中包含括号的情况)
        $sqlTpl = preg_replace(';IN\s*\([,?\s]*\);i', "IN(?)", $sqlTpl);

        //替换包含数字的表和字段(由于统一替换，所以最终的去重不受影响)
        $sqlTpl = preg_replace(';([a-zA-Z_]+)(?:\d+);', "$1xxx", $sqlTpl);

        //批量insert/replace into后面的values只保留一个
        if(preg_match(";(?:\bINSERT\b|\bREPLACE\b)\s+\w*\s+\bINTO\b;i", $sqlTpl, $matches)){
            $pattern = ";VALUES\s*(\([^)]++\))(?:\s*,\s*\([^)]++\))*;i";

            $sqlTpl = preg_replace($pattern, "VALUES $1", $sqlTpl);
        }

        //将多个空格转换为一个空格
        $pattern = ';[\s\n\r\0\x0B]+;';
        $sqlTpl = preg_replace($pattern, ' ', $sqlTpl);

        //去除两边的空格
        $sqlTpl = trim($sqlTpl);
        return $sqlTpl; 
    }
}
