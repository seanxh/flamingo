#!/usr/bin/env python
# -*- coding: UTF-8 -*-
import database
import multiprocessing
import commands
import time

'''
@version : 2013-09-29 1.0
@author : xuhao05
@todo : auto excute the multi api monitor shell commands
'''

#监控PHP脚本所在目录
DAEMON_PATH = "/opt/www/monitor.api.rms.baidu.com/service/mon_common_api/daemon/crontab/cron_mon_api.php"
#php-cli 所在目录
PHP_CLI = "/home/work/local/php/bin/php"
#本脚本执行日志，记录的文件
LOG_PATH = "/data/www/monitor.api.rms.baidu.com/cron_"+time.strftime('%Y%m%d')+".log"

##数据库设置
MYSQL_HOST = '127.0.0.1:3308'
MYSQL_DB = 'monitor'
MYSQL_USER = 'root'
MYSQL_PASS = ''

global db
db = database.Connection(host=MYSQL_HOST,
                           database=MYSQL_DB,user=MYSQL_USER,
                           password=MYSQL_PASS)


'''
@todo 获取共有多少接口类型
'''
def get_api_types():
    config_api_type = db.config_api_type
    api_types = config_api_type.select() 
    return api_types
 
'''
@todo 获取某接口类型下共有几个isp
'''
def get_api_isps(api_type):
    isp_ip_info = db.isp_ip_info
    return [result.id for result in isp_ip_info.where( isp_ip_info.api_type==str(api_type)).select()]

'''
@todo 获取某接口类型下共有几个cron
'''
def get_api_cron_num(api_type):
    config_common_api = db.config_common_api
    return [result.cron_num for result in config_common_api.fields('distinct(cron_num)').where(config_common_api.api_type==str(api_type)).select()]

'''
@todo fork 一个进程执行监控，并记录日志
'''
class Monitor(multiprocessing.Process):
    def __init__(self,api_name,isp_num,cron_num):
        multiprocessing.Process.__init__(self)
        shell =  PHP_CLI + ' ' + DAEMON_PATH + ' ' + api_name+' '+ str(isp_num) + ' ' + str(cron_num)
        self.shell = shell
    def run(self):
        start = time.time()
	(status,result) = commands.getstatusoutput(self.shell)
        end = time.time()
        exec_time = round(end-start,3)
        log = open(LOG_PATH, 'a')
        log.write("%s|%s|%d|%s|%f\n" % (time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(start)),self.shell,status,result,exec_time ) ) 
        log.close()


#自动生成这些 shell 命令，并fork进程去执行，然后退出
# /opt/www/monitor.api.rms.baidu.com/service/mon_common_api/daemon/crontab/cron_mon_api.php rms 1 1
if __name__ == '__main__' :
    #test = db.isp_ip_info
    monitor_processes = [] 
    api_types = get_api_types()
    for api_type in api_types:
        api_isps = get_api_isps(api_type.id)
        cron_nums = get_api_cron_num(api_type.id)
        for isp in api_isps:
            for cron_num in cron_nums:
                monitor_processes.append( Monitor(api_type.name,isp,cron_num) )

    for process in monitor_processes:
        process.start()
    
    for process in monitor_processes:
        process.join()
