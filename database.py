#!/usr/bin/env python
# -*- coding: UTF-8 -*-
 
"""基于Python 的MySQLdb的一个简单封闭
@version: 1.0
@author: xuhao3 REF from http://www.cnblogs.com/Alexander-Lee/archive/2010/08/23/1806674.html
@see: http://www.cnblogs.com/Alexander-Lee/archive/2010/08/23/1806674.html
"""
 
import copy
import MySQLdb.constants
import MySQLdb.converters
import itertools
import logging
 
class Connection(object):
    """
    @todo: 构造函数
    @param  host: 127.0.0.1:8080
    @param database: databaseName
    @param user: 用户名
    @param password:密码
    """
    def __init__(self, host, database, user=None, password=None):
        self.host = host
        self.database = database
        #conv将文字映射到Python类型的字典。默认为MySQLdb.converters.conversions
        args = dict(conv=CONVERSIONS,# use_unicode=True, charset="utf8",
                    db=database, init_command='SET time_zone = "+8:00"',
                    sql_mode="TRADITIONAL")
        if user is not None:
            args["user"] = user
        if password is not None:
            args["passwd"] = password
 
        # We accept a path to a MySQL socket file or a host(:port) string
        if "/" in host:
            args["unix_socket"] = host
        else:
            self.socket = None
            pair = host.split(":")
            if len(pair) == 2:
                args["host"] = pair[0]
                args["port"] = int(pair[1])
            else:
                args["host"] = host
                args["port"] = 3306
 
        self._db = None
        self._db_args = args
        try:
            self.reconnect()
 
        except:
            logging.error("Cannot connect to MySQL on %s", self.host,
                          exc_info=True)
 
    """
    @todo 析构函数
    """
    def __del__(self):
        self.close()
 
    """
    @todo 关闭数据库连接
    """
    def close(self):
        """Closes this database connection."""
        if self._db is not None:
            self._db.close()
            self._db = None
 
    """
    @todo 提交更改
    """
    def commit(self):
        if self._db is not None:
            try:
                self._db.ping()
            except:
                self.reconnect()
            try:
                self._db.commit()
            except Exception,e:
                self._db.rollback()
                logging.exception("Can not commit",e)
 
    """
    @todo 回滚
    """
    def rollback(self):
        if self._db is not None:
            try:
                self._db.rollback()
            except Exception,e:
                logging.error("Can not rollback")
 
    """
    @todo 连接数据库
    """
    def reconnect(self):
        """关闭已经连接的数据库，重新打开一个"""
        self.close()
        self._db = MySQLdb.connect(**self._db_args)
        #self._db.autocommit(False)
        self._db.autocommit(True)
 
    """
    @todo 执行sql语句
    @param query: sql语句
    @param parameters:参数
    """
    def query(self, query, *parameters):
        """Returns a row list for the given query and parameters."""
        cursor = self._cursor()
        try:
            self._execute(cursor, query, parameters)
            """ cursor.description 格式如下：(('id', 3, 1, 10, 10, 0, 0), ('name', 253, 2, 50, 50, 0, 0)) """
            column_names = [d[0] for d in cursor.description]
            return [Row(itertools.izip(column_names, row)) for row in cursor]
        finally:
            cursor.close()
 
    """
    @todo 获取sql的第一行
    """
    def get(self, query, *parameters):
        """Returns the first row returned for the given query."""
        rows = self.query(query, *parameters)
        if not rows:
            return None
        else:
            return rows[0]
 
    """
    @todo 执行sql语句，包含delete,update等
    """
    def execute(self, query, *parameters):
        """Executes the given query, returning the lastrowid from the query."""
        cursor = self._cursor()
        try:
            self._execute(cursor, query, parameters)
            return cursor.lastrowid
        finally:
            cursor.close()
 
    """
    @todo 返回sql的第一行，第一列
    """
    def count(self,query, *parameters):
        """Executes the given query, returning the count value from the query."""
        cursor = self._cursor()
        try:
            cursor.execute(query, parameters)
            return cursor.fetchone()[0]
        finally:
            cursor.close()
 
    """
    @todo 当执行db.table_name时，返回此结果
    """
    def __getitem__(self, tablename) :
        '''
        return single table queryer for select table
        '''
        return TableQueryer(self,tablename)
 
    """
    @todo 当执行db['talbe_name']时，返回此结果
    """
    def __getattr__(self,tablename):
        '''
        return single table queryer for select table
        '''
        return TableQueryer(self,tablename)
 
    def fromQuery(self,Select):
        '''
        return single table queryer for query
        '''
        return TableQueryer(self,Select)
 
    """
    @todo 插入数据
    """
    def insert(self,table,**datas):
        '''
        Executes the given parameters to an insert SQL and execute it
        '''
        return Insert(self,table)(**datas)
 
    """
    @todo 批量执行sql
    """
    def executemany(self, query, parameters):
        """Executes the given query against all the given param sequences.
 
        We return the lastrowid from the query.
        """
        cursor = self._cursor()
        try:
            cursor.executemany(query, parameters)
            return cursor.lastrowid
        finally:
            cursor.close()
 
    def _cursor(self):
        if self._db is None: self.reconnect()
        try:
            self._db.ping()
        except:
            self.reconnect()
        return self._db.cursor()
 
    def _execute(self, cursor, query, parameters):
        try:
            return cursor.execute(query, parameters)
        except OperationalError:
            logging.error("Error connecting to MySQL on %s", self.host)
            self.close()
            raise
 
"""
执行db.table_name时返回的table query 对象
"""
class TableQueryer:
    '''
    Support for single table simple querys
    '''
    def __init__(self,db,tablename):
        self.tablename=tablename
        self.db=db
        self._init_sql_claude()
 
    def _init_sql_claude(self):
        if self.__dict__.has_key('_sql_claude') :
            return_value = self._sql_claude
        else :
            return_value = None
        self._sql_claude = {
            'where' : [],
            'sort_fields' : [],
            'limit' : None,
            'fields' : [],
            'groups' : [],
            'having' : None
        }
        return return_value
 
    '''
    @todo 插入数据
    '''
    def insert(self,*datas,**fields):
        return Insert(self.db,self.tablename)(*datas,**fields)
 
    def select(self,query=None):
        sql_claude = self._init_sql_claude()
        return Select(self.db,self.tablename,sql_claude)(query)
 
    def update(self,*datas,**fields):
        sql_claude = self._init_sql_claude()
        return Update(self.db,self.tablename,sql_claude)(*datas,**fields)
 
    def delete(self,query=None):
        sql_claude = self._init_sql_claude()
        return Delete(self.db,self.tablename,sql_claude)(query)
 
    def count(self,query=None):
        self._sql_claude['fields'] = ['count(*)']
        sql_claude = self._init_sql_claude()
        return self.db.count(Select(self.db,self.tablename,sql_claude).get_sql(query))
    """@todo 返回结果集的第一行"""
    def get(self,query=None):
        sql_claude = self._init_sql_claude()
        return self.db.get(Select(self.db,self.tablename,sql_claude).get_sql(query))
 
    def get_sql(self,query=None):
        sql_claude = self._init_sql_claude()
        return Select(self.db,self.tablename,sql_claude).get_sql(query)
 
    def where(self,query):
        if isinstance(query,conds):
            self._sql_claude['where'].append(query.get_sql())
        else :
            self._sql_claude['where'].append(query)
        return self
 
    def sort(self,**fields):
        del self._sql_claude['sort_fields'][:]
        for key in fields.keys():
            self._sql_claude['sort_fields'].append("".join(["`",key,"` ",fields[key]]))
        return self
 
    def limit(self,start,count=None):
        if count != None:
            self._sql_claude['limit']="".join(["LIMIT ",str(start),",",str(count)])
        else:
            self._sql_claude['limit']="".join(["LIMIT ",str(start)])
        return self
 
    """@todo select的选取column"""
    def fields(self,*fields):
        if len(fields):
            self._sql_claude['fields'] += fields
        return self   
 
    def group_by(self,*fields):
        if len(fields)<1:
            raise OperationalError,"Must have a field"
        for f in fields:
            self._sql_claude['groups'].append(f)
        return self
 
    def having(self,cond):
        self._sql_claude['having']=cond
        return self
 
    """
    @todo 执行db.table_name.id 时返回字段
    """
    def __getattr__(self,field_name):
        return conds(field_name)
 
class Select:
    '''
    Select list with current where clouse
    '''
    def __init__(self,db,tablename,claude):
        self.db=db
        self._tablename=tablename
        self._sql_claude=claude
 
    def get_sql(self,query):
 
        if query != None :
            self._sql_claude['where'].append(query)
 
        _sql_slice=["SELECT "]
        if self._sql_claude['fields']:
            #_sql_slice.append(",".join(["".join(["`",str(f),"`"]) for f in self._sql_claude['fields']]))
            _sql_slice.append(",".join( [str(f) for f in self._sql_claude['fields']] ))
        else:
            _sql_slice.append("*")
        _sql_slice.append(" FROM `")
        _sql_slice.append(self._tablename)
        _sql_slice.append("`")
 
        _sql_slice.append(" ")
 
        if self._sql_claude['where']:
            _sql_slice.append(" WHERE ")
            _sql_slice.append(" and ".join(self._sql_claude['where']))
            #_sql_slice.append(self._where.get_sql())
            _sql_slice.append(" ")
        if len(self._sql_claude['groups'])>0:
            _sql_slice.append("GROUP BY ")
            _sql_slice.append(",".join([ "`"+f+"`" for f in self._sql_claude['groups']]))
            if self._sql_claude['having']:
                _sql_slice.append(" HAVING ")
                _sql_slice.append(self._sql_claude['having'])
                _sql_slice.append(" ")
 
        if self._sql_claude['sort_fields']:
            _sql_slice.append("ORDER BY ")
            _sql_slice.append(",".join([s for s in self._sql_claude['sort_fields']]))
 
        if self._sql_claude['limit']:
            _sql_slice.append(" ")
            _sql_slice.append(self._sql_claude['limit'])
 
        return "".join(_sql_slice)
 
    def __call__(self,query):
        _sql=self.get_sql(query)
        return self.db.query(_sql)
 
class Update(object):
    '''
    Update Query Generator
    '''
    def __init__(self,db,tablename,claude):
        self.db=db
        self._tablename=tablename
        self._sql_claude=claude
 
    def get_sql(self,**fields):
        _cols = [ column+'='+str(fields[column])  if isinstance(fields[column],int)  \
                 else column+'='+ '"' + fields[column] + '"' \
                 for column in fields];
        _sql_slice=["UPDATE ","`"+self._tablename+"`"," SET ",",".join(_cols)]
        if self._sql_claude['where']:
            _sql_slice.append(" WHERE ")
            _sql_slice.append(" and ".join(self._sql_claude['where']))
            #_sql_slice.append(self._where.get_sql())
        if self._sql_claude['sort_fields']:
            _sql_slice.append("ORDER BY ")
            _sql_slice.append(",".join([s for s in self._sql_claude['sort_fields']]))
        if self._sql_claude['limit']:
            _sql_slice.append(" ")
            _sql_slice.append(self._sql_claude['limit'])
        return "".join(_sql_slice)
 
    def __call__(self,*fields,**dicts):
        if len(fields)<1 and len(dicts) < 1:
            raise OperationalError,"Must have unless 1 field to update"
        if len(fields)>0:
            dicts = fields[0]
        _sql = self.get_sql(**dicts)
        return self.db.execute(_sql)
 
class Delete(object):
    def __init__(self,db,tablename,claude):
        self.db=db
        self._tablename=tablename
        self._sql_claude=claude
 
    def get_sql(self,query=None):
        if query != None :
            self._sql_claude['where'].append(query)
 
        _sql_slice=["DELETE FROM `",self._tablename,"`"]
        if  len(self._sql_claude['where'])<1 and self._sql_claude['limit']==None:
            raise OperationalError,"Delete Muast Have a Limit or Where Option"
        if self._sql_claude['where']:
            _sql_slice.append(" WHERE ")
            _sql_slice.append(" and ".join(self._sql_claude['where']))
            #_sql_slice.append(self._where.get_sql())
        if self._sql_claude['sort_fields']:
            _sql_slice.append("ORDER BY ")
            _sql_slice.append(",".join([s for s in self._sql_claude['sort_fields']]))
        if self._sql_claude['limit']:
            _sql_slice.append(" ")
            _sql_slice.append(self._sql_claude['limit'])
        return "".join(_sql_slice)
 
    def __call__(self,query=None):
        _sql = self.get_sql(query)
        return self.db.execute(_sql)
 
class Insert(object):
    '''
    Insert Query Generator
    '''
    def __init__(self,db,tablename):
        self.db=db
        self.tablename=tablename
 
    def __call__(self,*fields,**dicts):
        if len(fields)<1 and len(dicts) < 1:
            raise OperationalError,"Must have unless 1 field to Insert"
        datas = []
 
        ##如果通过insert(id=1,name='sean')的方式传递参数
        if len(dicts) != 0:
            datas.append(dicts)
 
        if len(fields) > 0:
            ##如果通过
            ##insert( [{'id':1,'name':'sean'},{'id':2,'name':'sean2'}])或
            ##insert( ({'id':1,'name':'sean'},{'id':2,'name':'sean2'}) ) 方式
            if len(fields) == 1 and not isinstance(fields[0], dict):
                fields = fields[0]
            ##如果通过insert({'id':1,'name':'sean'},{'id':2,'name':'sean2'})
            else:
                pass
            for data in fields:
                datas.append(data)
        ##要求每个dict的key都必须完全一样
        columns = datas[0].keys()
        for data in datas:
            columns_tmp = data.keys()
            if len(columns_tmp) != len(columns):
                 raise OperationalError,"The insert Data Must have the Same fields"
            for key in columns_tmp:
                if columns.index(key) == -1:
                    raise OperationalError,"The insert Data Must have the Same fields"
 
        _prefix="".join(['INSERT INTO `',self.tablename,'`'])
        _fields=",".join(["".join(['`',column,'`']) for column in columns])
        _values = []
        for dicts in datas:
            _values.append( "(" +
                            ",".join(
                                     [ str(dicts[key]) if isinstance(dicts[key],int) \
                                      else "'"+dicts[key]+"'" \
                                       for key in dicts])
                            + ")")
        _sql="".join([_prefix,"(",_fields,") VALUES ",",".join(_values) ])
        return self.db.execute(_sql)
 
"""对dict的一个扩展，支持dict.attr访问"""
class Row(dict):
    def __getattr__(self, name):
        try:
            return self[name]
        except KeyError:
            raise AttributeError(name)
 
"""
@todo 将关系数据库的字段属性的一些操作符重载。如 table.id==1
@param field: 数据库column
"""
class conds(object):
    def __init__(self,field):
        self.field_name=field
        self._sql=""
        self._params=[]
        self._has_value=False
        self._sub_conds=[]
 
    def _prepare(self,sql,value):
        if not self._has_value:
            self._sql=sql
            self._params.append(value)
            self._has_value=True
            return self
        raise OperationalError,"Multiple Operate conditions"
 
    def get_sql(self,tn=None):
        _sql_slice=[]
        _sql_slice.append(self._sql)
        _sql_slice.append(" ")
        #如果有条件判断and或or
        if len(self._sub_conds):
            for cond in self._sub_conds:
                _sql_slice.append(cond[1])
                _sql_slice.append(cond[0].get_sql())
        _where = "".join(_sql_slice) % tuple([ str(param) if isinstance(param,int) else "'"+param+"'" for param in self._params])
        #tn表示前缀，给每个`column`都加上前缀
        if tn:
            import re
            p=compile(r'`(\w*?)`')
            _where = p.sub(r'`%s.\1`'%tn,_where)
        return _where
 
    """不等于 !="""
    def __ne__(self,value):
        return self._prepare("".join(["`",self.field_name,'`','<>%s']),value)
 
    """等于  =="""
    def __eq__(self,value):
        if not self._has_value:
            #如果是database.conds类实例
            if str(value.__class__)=="database.conds":
                self._sql="".join(["`",self.field_name,'`','=',value.get_sql()])
                self._params.append(value.get_params()[0])
            else:
                self._sql="".join(["`",self.field_name,'`','=%s'])
                self._params.append(value)
            self._has_value=True
            return self
        raise OperationalError,"Multiple Operate conditions"
 
    """like"""
    def like(self,value):
        return self._prepare("".join(["`",self.field_name,'`',' like %s']),value)
 
    """小于等于 <="""
    def __le__(self,value):
        return self._prepare("".join(["`",self.field_name,'`','<=%s']),value)
    """小于 <"""
    def __lt__(self,value):
        return self._prepare("".join(["`",self.field_name,'`','<%s']),value)
    """大于 >"""
    def __gt__(self,value):
        return self._prepare("".join(["`",self.field_name,'`','>%s']),value)
    """大于等于 >="""
    def __ge__(self,value):
        return self._prepare("".join(["`",self.field_name,'`','>=%s']),value)
 
    """in查询"""
    def In(self,array):
        if not self._has_value:
            _values=",".join(["".join(['\'',str(i),'\'']) for i in array])
            self._sql="".join(["`",self.field_name,'`',' in (',_values,")"])
            self._has_value=True
            return self
        raise OperationalError,"Multiple Operate conditions"
 
    """not in的查询"""
    def Not_In(self,array):
        if not self._has_value:
            _values=",".join(["".join(['\'',str(i),'\'']) for i in array])
            self._sql="".join(["`",self.field_name,'`',' not in (',_values,")"])
            self._has_value=True
            return self
        raise OperationalError,"Multiple Operate conditions"
 
    """处理&符"""
    def __and__(self,cond):
        if self._has_value:
            self._sub_conds.append((cond," AND "))
            return self
        raise OperationalError,"Operation with no value"
    """处理||符"""
    def __or__(self,cond):
        if self._has_value:
            self._sub_conds.append((cond," OR "))
            return self
        raise OperationalError,"Operation with no value"
 
#保证unicode/binary 正常转换
FIELD_TYPE = MySQLdb.constants.FIELD_TYPE
#标志结果集中的列属性
FLAG = MySQLdb.constants.FLAG
# 注：在最新下载的MySQLdb2中，此常量已经消失，而且MySQLdb.connect的conv参数也已经木有了
CONVERSIONS = copy.deepcopy(MySQLdb.converters.conversions)
for field_type in \
        [FIELD_TYPE.BLOB, FIELD_TYPE.STRING, FIELD_TYPE.VAR_STRING] + \
        ([FIELD_TYPE.VARCHAR] if 'VARCHAR' in vars(FIELD_TYPE) else []):
    #在列表的头部插入此格式，将这些类型的字段优先转换为二进制字符串
    CONVERSIONS[field_type].insert(0, (FLAG.BINARY, str))

#由于有时机器内容对浮点数和长整型的处理方式不太一样，有时会把int全部当做long返回。
#如果你需要强制转换 可以加上 FIELD_TYPE.LONG: int 。LONG都会变成INT。
#reference:http://stackoverflow.com/questions/12898516/python-mysqldb-converters-isnt-working
#声明一些Exception的异常类型
IntegrityError = MySQLdb.IntegrityError
OperationalError = MySQLdb.OperationalError
