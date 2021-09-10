<?

    function package_have_return($mb_id,$have=0){
        $my_package = [];

        if($have==1){
            $where  = "AND promote = 1 ";
        }else if($have==0){
            $where  = "AND promote != 1 ";
        }

        $sql_r = "SELECT count(*) as cnt from package_p1 WHERE mb_id = '{$mb_id}' ".$where;
        $result = sql_fetch($sql_r)['cnt'];
        array_push($my_package,$result);

        $sql_r = "SELECT count(*) as cnt from package_p2 WHERE mb_id = '{$mb_id}' ".$where;
        $result = sql_fetch($sql_r)['cnt'];
        array_push($my_package,$result);

        $sql_r = "SELECT count(*) as cnt from package_p3 WHERE mb_id = '{$mb_id}' ".$where;
        $result = sql_fetch($sql_r)['cnt'];
        array_push($my_package,$result);

        $sql_r = "SELECT count(*) as cnt from package_p4 WHERE mb_id = '{$mb_id}' ".$where;
        $result = sql_fetch($sql_r)['cnt'];
        array_push($my_package,$result);

        $sql_r = "SELECT count(*) as cnt from package_p5 WHERE mb_id = '{$mb_id}' ".$where;
        $result = sql_fetch($sql_r)['cnt'];
        array_push($my_package,$result);

        $sql_r = "SELECT count(*) as cnt from package_p6 WHERE mb_id = '{$mb_id}' ".$where;
        $result = sql_fetch($sql_r)['cnt'];
        array_push($my_package,$result);

        $sql_r = "SELECT count(*) as cnt from package_p7 WHERE mb_id = '{$mb_id}' ".$where;
        $result = sql_fetch($sql_r)['cnt'];
        array_push($my_package,$result);

        

        return $my_package;
    }

    function my_total_package($mb_id){
        $total_package_sql = "
        select sum(total) as total FROM (
            SELECT COUNT(*) AS total FROM package_p1 WHERE mb_id = '{$mb_id}' AND promote != 1 
                union all 
            SELECT COUNT(*) AS total FROM package_p2 WHERE mb_id = '{$mb_id}' AND promote != 1  
                union all 
            SELECT COUNT(*) AS total FROM package_p3 WHERE mb_id = '{$mb_id}' AND promote != 1  
                union all
            SELECT COUNT(*) AS total FROM package_p4 WHERE mb_id = '{$mb_id}' AND promote != 1  
                union all 
            SELECT COUNT(*) AS total FROM package_p5 WHERE mb_id = '{$mb_id}' AND promote != 1  
                union all  
            SELECT COUNT(*) AS total FROM package_p6 WHERE mb_id = '{$mb_id}' AND promote != 1  
                union all 
            SELECT COUNT(*) AS total FROM package_p7 WHERE mb_id = '{$mb_id}' AND promote != 1  
            ) tb
        ";

        $total = sql_fetch($total_package_sql)['total'];

        if($total < 1){$total = '-';}
        return $total;
    }
?>