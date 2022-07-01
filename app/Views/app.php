<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>NRB Currently Opened File List</title>
        <link rel="stylesheet" href="css/index.css">
        <script src="js/vue.js"></script>
        <script src="js/axios.min.js"></script>
        <script src="js/index.js"></script>
        <script src="js/en.js"></script>
        <script src="js/data-tables.min.js"></script>
    </head>
    <body style="font-family: Helvetica Neue,Helvetica,PingFang SC,Hiragino Sans GB,Microsoft YaHei,SimSun,sans-serif;">
        <div id='app'>
            <data-tables 
                         :data='tableData'
                         :table-props='tableProps'
                         :pagination-def="paginationDef"
                         :checkbox-filter-def='getCheckFilterDef()'
                         :row-action-def='getRowActionsDef()'>
                <el-table-column prop="share" label="Share" sortable="custom" width="128px">
                </el-table-column>
                <!-- <el-table-column prop="computer" label="Computer" sortable="custom" width="128px">
                    </el-table-column> -->
                <el-table-column prop="user" label="User" sortable="custom" width="128px">
                </el-table-column>
                <el-table-column prop="file" label="File" sortable="custom">
                </el-table-column>
            </data-tables>
        </div>
        <script src="js/app.js"></script>
    </body>
</html>
