Flow Monitor Bundle  
=====================================  
This bundle allow to monitor akeneo batch job execution with triggers.
At the time only triggers of filesystem type are accepted.
  
Installation
----------
 

 - Add bundle into AppKernel.php
 
 - Declare a trigger into config.yml
```  
#config.yml
flow_monitoring: 
    triggers:  
        my_custom_trigger: 
            filesystem: # only filesystem is supported
                directory: /tmp #(**)
                data_file_extension: null     
                trigger_file_pattern: csv_product_export-\d{8}.order #(*)    
                run_extension: run     
                done_extension: done
	    #another_trigger
```
- Declare a flow into config.yml
 ```
 #config.yml
flow_monitoring: 
    my_custom_flow:
        trigger: my_custom_trigger
        job_instance_name: csv_product_export (\*\*\*\*\*)
        ##default values
        #user: admin 
    #another_flow
```

- Configure crontab to execute symfony console 
```
${root_projet_dir} flow-monitor-run --env=prod --no-debug
```

**Explanation:**
With configuration below when ${root_projet_dir} flow-monitor-run --env=prod --no-debug is executed, if a file corresponding to csv_product_export-\d{8}.order (*) pattern is found into /tmp (\*\*) a job_execution and job_queue is created using {job_instance_name} (\*\*\*\*\*) job instance
When akeneo:batch:job-queue-consumer-daemon consume the created job execution, the file csv_product_export-\d{8}.order (\*) is renamed csv_product_export-\d{8}.{run_extension} (\*\*\*) and when job is completed (success or error) the file is renamed  csv_product_export-\d{8}.{done_extension} (\*\*\*\*)

Of course you can configure multiple triggers and flows
 