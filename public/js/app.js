var dataTables = DataTables.default;

ELEMENT.locale(ELEMENT.lang.en)

var app = new Vue({
    components: {dataTables},
    data() {
        return {
            timer: '',
            tableData: [],
            tableProps: {
                size: 'mini',
                stripe: true,
            },
            paginationDef: {
                pageSize: 20,
                pageSizes: [10,20,50,100,250,500]
            }
        }
    },
    created() {
        this.updateFileList()
        this.timer = setInterval(this.updateFileList, 60000);
    },
    beforeDestroy() {
        this.cancelUpdateFilelist()
    },
    methods: {
        updateFileList() {
            console.log('we are going to update')
            axios.get('/api/files/') 
                .then(response => {
                    this.tableData = response.data.data
                })
                .catch(e => {
                    this.errors.push(e)
                })
        },
        cancelUpdateFileList() {
            clearInterval(this.timer)
        },
        getActionsDef() {
            let self = this;
            return {
                width: 5,
                def: [{
                    name: 'new',
                    handler() {
                        self.$message('new clicked')
                    },
                    icon: 'plus'
                }, {
                    name: 'import',
                    handler() {
                        self.$message('import clicked')
                    },
                    icon: 'upload'
                }]
            }
        },
        getCheckFilterDef() {
            return {
                width: 14,
                props: 'share',
                def: [                {
                    'code': 'acad',
                    'name': 'ACAD'
                }, {
                    'code': 'almar',
                    'name': 'Almar'
                }, {
                    'code': 'commercial',
                    'name': 'Commercial'
                }, {
                    'code': 'common',
                    'name': 'Common'
                }, {
                    'code': 'costing',
                    'name': 'Costing'
                }, {
                    'code': 'marketing',
                    'name': 'Marketing'
                }, {
                    'code': 'photos',
                    'name': 'Photos'
                }, {
                    'code': 'production',
                    'name': 'Production'
                }, {
                    'code': 'recreation',
                    'name': 'Recreation'
                }, {
                    'code': 'scans',
                    'name': 'Scans'
                }]
            }
        },
        getRowActionsDef() {
            let self = this
            return [{
                type: 'primary',
                handler(row) {
                    self.$message('Edit clicked')
                    console.log('Edit in row clicked', row)
                },
                name: 'Edit'
            }, {
                type: 'primary',
                handler(row) {
                    self.$message('RUA in row clicked')
                    console.log('RUA in row clicked', row)
                },
                name: 'RUA'
            }]
        }
    }
}).$mount('#app');
