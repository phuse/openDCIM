<?php
class LogicalService {
        var $ServiceID;
        var $ServiceName;
        var $ServiceCode;
        var $SOM;
        var $ServiceColor;

        function MakeSafe(){
                $this->ServiceID=intval($this->ServiceID);
                $this->ServiceName=addslashes(trim($this->ServiceName));
                $this->ServiceCode=intval($this->ServiceCode);
                $this->SOM=addslashes(trim($this->SOM));
                $this->ServiceColor=addslashes(trim($this->ServiceColor));
        }
        function MakeDisplay(){
                $this->ServiceName=stripslashes($this->ServiceName);
                $this->ServiceCode=($this->ServiceCode);
                $this->SOM=stripslashes($this->SOM);
                $this->ServiceColor=stripslashes($this->ServiceColor);
        }
        static function RowToObject($row){
                $svc=new LogicalService();
                $svc->ServiceID=$row["EISServiceID"];
                $svc->ServiceName=$row["ServiceName"];
                $svc->ServiceCode=$row["ServiceCode"];
                $svc->SOM=$row["SOM"];
                $svc->ServiceColor=$row["ServiceColor"];

                $svc->MakeDisplay();

                return $svc;
        }
        function query($sql){
                global $dbh;
                return $dbh->query($sql);
        }

        function GetServiceList() {
                $sql="SELECT * FROM fac_EISservice ORDER BY ServiceName ASC;";
                $ServiceList=array();
                foreach($this->query($sql) as $row){
                        $ServiceList[]=LogicalService::RowToObject($row);
                }

                return $ServiceList;
        }
        function GetServiceByID() {
                $this->MakeSafe();

                $sql="SELECT * FROM fac_EISService WHERE ServiceID=$this->ServiceID;";

                if($row=$this->query($sql)->fetch()){
                        foreach(LogicalService::RowToObject($row) as $prop => $value){
                                $this->$prop=$value;
                        }
                }else{
                        // Return an empty object in the case of a failed lookup, preserve the id though
                        foreach($this as $prop => $value){
                                $this->$prop=($prop=='ServiceID')?$value:'';
                        }
                }
                return true;
        }

}
?>
