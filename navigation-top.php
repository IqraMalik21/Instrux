<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

<script type="text/javascript">
 var expenseweekly=0;
    var budgetweekly=0;

    var expensedaily=0;
    var budgetdaily=0;

    var expensemonthly=0;
    var budgetmonthly=0;

    var voltminalert=0;
    var minimumvoltagereal=0;

    var voltmaxalert=0;
    var maximumvoltagereal=0;

    var currentminalert=0;
    var minimumcurrentreal=0;

    var currenttmaxalert=0;
    var maximumcurrentreal=0;

    var n=0;

     /*function notifbudget(){
        var i=document.createElement('i');
        i.setAttribute('class','fa fa-money');
        i.appendChild(document.createTextNode("Budget Alert"));
        var br=document.createElement('br');
        i.appendChild(br);
        return i;
        }
         document.getElementById("notif").appendChild(notifbudget());*/

function drawAlert() {
   

$.ajax({
      url: "alert_data.php?",
      dataType: "json",
      async: true,
      success: function (obj) {
       /* document.getElementById("a").innerHTML= obj.extras.expenseweekly;
        document.getElementById("b").innerHTML= obj.extras.budgetweekly;
        document.getElementById("c").innerHTML= obj.extras.expensemonthly;
        document.getElementById("d").innerHTML= obj.extras.budgetmonthly;

        document.getElementById("e").innerHTML= obj.extras.voltminalert;
        document.getElementById("f").innerHTML= obj.extras.minimumvoltagereal;
        document.getElementById("g").innerHTML= obj.extras.voltmaxalert;
        document.getElementById("h").innerHTML= obj.extras.maximumvoltagereal;

        document.getElementById("i").innerHTML= obj.extras.currentminalert;
        document.getElementById("j").innerHTML= obj.extras.minimumcurrentreal;
        document.getElementById("k").innerHTML= obj.extras.currentmaxalert;
        document.getElementById("l").innerHTML= obj.extras.maximumcurrentreal;*/
         if(n!=0){
        var strMessage1 = document.getElementById("contentSectionID");
        strMessage1.innerHTML="";
        }


        expenseweekly=obj.extras.expenseweekly;
        budgetweekly=obj.extras.budgetweekly;

        expensedaily=obj.extras.expensedaily;
        budgetdaily=obj.extras.budgetdaily;

        expensemonthly=obj.extras.expensemonthly;
        budgetmonthly=obj.extras.budgetmonthly;

        voltminalert=obj.extras.voltminalert;
        minimumvoltagereal=obj.extras.minimumvoltagereal;
        voltmaxalert=obj.extras.voltmaxalert;
        maximumvoltagereal=obj.extras.maximumvoltagereal;

        currentminalert=obj.extras.currentminalert;
        minimumcurrentreal=obj.extras.minimumcurrentreal;
        currentmaxalert=obj.extras.currenttmaxalert;
        maximumcurrentreal=obj.extras.maximumcurrentreal;


        //******* BUDGET ALERTS **********
        if(expensedaily>budgetdaily){

        function createList(expensedaily){
        
        
        var listViewItem=document.createElement('li');
        listViewItem.setAttribute('class','dropdown-item');
        var i=document.createElement('i');
        i.setAttribute('class','fa fa-money');
        i.appendChild(document.createTextNode("Daily expense "+expensedaily+" PKR has crossed your budget("+budgetdaily+" PKR)"));
        listViewItem.appendChild(i);
        return listViewItem;
      }
      document.getElementById("contentSectionID").appendChild(createList(expensedaily));
        }
       

        if(expenseweekly>budgetweekly){

        function createList(expenseweekly){
        
        var listViewItem=document.createElement('li');
        listViewItem.setAttribute('class','dropdown-item');
        var i=document.createElement('i');
        i.setAttribute('class','fa fa-money');
        i.appendChild(document.createTextNode(" Weekly expense "+expenseweekly+" PKR has crossed your budget("+budgetweekly+" PKR)"));
        listViewItem.appendChild(i);
        return listViewItem;
      }
      document.getElementById("contentSectionID").appendChild(createList(expenseweekly));
        }

        if(expensemonthly>budgetmonthly){

        function createList(expensemonthly){
        
        var listViewItem=document.createElement('li');
        listViewItem.setAttribute('class','dropdown-item');
        var i=document.createElement('i');
        i.setAttribute('class','fa fa-money');
        i.appendChild(document.createTextNode(" Monthly expense "+expensemonthly+" PKR has crossed your budget("+budgetmonthly+" PKR)"));
        listViewItem.appendChild(i);
        return listViewItem;
      }
      document.getElementById("contentSectionID").appendChild(createList(expensemonthly));
        }
       
        //*********** VOLTAGE ALERTS ************

        if(minimumvoltagereal<=voltminalert){

        function createList(voltminalert){
        
        var listViewItem=document.createElement('li');
        listViewItem.setAttribute('class','dropdown-item');
        var i=document.createElement('i');
        i.setAttribute('class','fa fa-bolt');
        i.appendChild(document.createTextNode("Voltage is below threshold ("+voltminalert+" V)"));
        listViewItem.appendChild(i);
        return listViewItem;
      }
      document.getElementById("contentSectionID").appendChild(createList(voltminalert));
        }

        if(maximumvoltagereal>=voltmaxalert){

        function createList(voltmaxalert){
        
        var listViewItem=document.createElement('li');
        listViewItem.setAttribute('class','dropdown-item');
        var i=document.createElement('i');
        i.setAttribute('class','fa fa-bolt');
        i.appendChild(document.createTextNode("Voltage crossed threshold ("+voltmaxalert+" V)"));
        listViewItem.appendChild(i);
        return listViewItem;
      }
      document.getElementById("contentSectionID").appendChild(createList(voltmaxalert));
        }


        //*********** CURRENT ALERTS ************

        if(minimumcurrentreal<=currentminalert){

        function createList(currentminalert){
        
        var listViewItem=document.createElement('li');
        listViewItem.setAttribute('class','dropdown-item');
        var i=document.createElement('i');
        i.setAttribute('class','fa fa-bolt');
        i.appendChild(document.createTextNode("Current is below threshold ("+currentminalert+" A)"));
        listViewItem.appendChild(i);
        return listViewItem;
      }
      document.getElementById("contentSectionID").appendChild(createList(currentminalert));
        }

        if(maximumcurrentreal>=currentmaxalert){

        function createList(currentmaxalert){
        
        var listViewItem=document.createElement('li');
        listViewItem.setAttribute('class','dropdown-item');
        var i=document.createElement('i');
        i.setAttribute('class','fa fa-bolt');
        i.appendChild(document.createTextNode("Current crossed threshold ("+currentmaxalert+" A)"));
        listViewItem.appendChild(i);
        return listViewItem;
      }
      document.getElementById("contentSectionID").appendChild(createList(voltmaxalert));
        }

      n++;}//success
       
      });//ajax
} //function


var myVar = setInterval(drawAlert, 10000);
//clearBox();
</script>

<!-- Top Navbar-->
<header class="header">
    <nav class="navbar">
        <div class="container-fluid">
        <div class="navbar-holder d-flex align-items-center justify-content-between">
            <div class="navbar-header"><a id="toggle-btn" href="#" class="menu-btn"><i class="icon-bars"> </i></a><a href="index.html" class="navbar-brand">
                <div class="brand-text d-none d-md-inline-block"><span>Instrux </span><strong class="text-primary"></strong></div></a></div>
            <ul class="nav-menu list-unstyled d-flex flex-md-row align-items-md-center">
            <!-- Notifications dropdown-->
            <li class="nav-item dropdown"> <a id="notifications" rel="nofollow" data-target="#" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link"><i class="fa fa-bell"></i><span class="badge badge-warning">*</span></a>
                <ul aria-labelledby="notifications" class="dropdown-menu">
                <li>
                    <div class="notification d-flex justify-content-between">
                        <div class="notification-content" id="contentSectionID"></div>
                        <!--<div class="notification-time"><small>4 hours ago</small></div>-->
                    </div></li>
                <!--<li><a rel="nofollow" href="#" class="dropdown-item"> 
                    <div class="notification d-flex justify-content-between">
                        <div class="notification-content"></div>
                        <div class="notification-time"><small>1 hour ago</small></div>
                    </div></a></li>-->
                <li><a rel="nofollow" href="alert.php" class="dropdown-item all-notifications text-center"> <strong> <i class="fa fa-bell"></i>View/Configure All Alerts</strong></a></li>
                </ul>
            </li>
            
            <!--<span id="a">0</span><br></br>
             <span id="b">0</span><br></br>
              <span id="c">0</span><br></br>
               <span id="d">0</span><br></br>
                <span id="e">0</span><br></br>
                <span id="f">0</span><br></br>
                <span id="g">0</span><br></br>
                <span id="h">0</span><br></br>
                <span id="i">0</span><br></br>
                <span id="j">0</span><br></br>
                <span id="k">0</span><br></br>
                <span id="l">0</span><br></br>-->
            <!-- Log out-->
            <li class="nav-item"><a href="logout.php" class="nav-link logout"> <span class="d-none d-sm-inline-block">Logout</span><i class="fa fa-sign-out"></i></a></li>
            </ul>
        </div>
        </div>
    </nav>
</header>