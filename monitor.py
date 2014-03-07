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

#报警PHP脚本所在目录
DAEMON_PATH = "a.php"
#php-cli 所在目录
PHP_CLI = "/usr/bin/php"
#本脚本执行日志，记录的文件
LOG_PATH = "/tmp/monitor_"+time.strftime('%Y%m%d')+".log"

##数据库设置
MYSQL_HOST = '127.0.0.1:3306'
MYSQL_DB = 'monitor'
MYSQL_USER = 'root'
MYSQL_PASS = 'root'

global db
db = database.Connection(host=MYSQL_HOST,
                           database=MYSQL_DB,user=MYSQL_USER,
                           password=MYSQL_PASS)
                           
db.execute('set names utf8')


'''
获取所有监控策略的报警周期
@return dict rule_cycle { rule_id : cycle(/s) }
'''
def calc_monitor_cycle():
    monitor_rules_models = db.monitor_rule
    monitor_rules = monitor_rules_models.where(monitor_rules_models.status==1).select()
    log_model = db.log_config
    logs = log_model.select()
    
    logs_dict = {}
    for log in logs:
        logs_dict[log.id] = log
    
    #gt_1min_rules = {}
    #lt_1min_rules = {}
    rules_cycle = {}
    
    for rule in monitor_rules:
        if rule.is_alert_everytime == 1:
            cycle = logs_dict[ rule.log_id ].log_cycle
        else:
            cycle = int(logs_dict[ rule.log_id ].log_cycle)* int(rule.alert_in_cycles)
        #if cycle >= 60 :
        rules_cycle[rule.id] = cycle
        #else:
        #    lt_1min_rules[rule.id] = cycle
    return rules_cycle


class ForkMonitor(multiprocessing.Process):
    def __init__(self,rules,time_stamp):
        multiprocessing.Process.__init__(self)
        self.daemon = True #如果设置此参数，则为后台线程
        self.rules = rules
        self.time_stamp = time_stamp
        
    def run(self):
        for rule in self.rules:
            if self.time_stamp % self.rules[rule] == 0:
                log = open(LOG_PATH, 'a')
                log.write("%s|%d\n" % (time.strftime('%Y-%m-%d %H:%M:%S',time.localtime(time.time())), rule ) ) 
                log.close()

#自动生成这些 shell 命令，并fork进程去执行，然后退出
# /opt/www/monitor.api.rms.baidu.com/service/mon_common_api/daemon/crontab/cron_mon_api.php rms 1 1
if __name__ == '__main__' :
    start = (int(time.time())//60)*60
    rules_cycle= calc_monitor_cycle()
    
    process_pool = []
    
    gt_1min_rules = {}
    lt_1min_rules = {}
    
    monitor = ForkMonitor(rules_cycle,start)
    monitor.start()
    process_pool.append( monitor )
    
    for rule in rules_cycle:
        if rules_cycle[rule] >=60: 
            gt_1min_rules[rule] = rules_cycle[rule]
        else:
            lt_1min_rules[rule] = rules_cycle[rule]
    
    while (time.time() - start ) < 60 :
        seconds = int( time.time() )
        monitor = ForkMonitor(lt_1min_rules,seconds)
        monitor.start()
        process_pool.append( monitor )
        time.sleep(1)
        
        
    for process in process_pool:
        process.join()