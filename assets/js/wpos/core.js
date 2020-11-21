/**
 * core.js is part of Wallace Point of Sale system (WPOS)
 *
 * core.js is the main object that provides base functionality to the WallacePOS terminal.
 * It loads other needed modules and provides authentication, storage and data functions.
 *
 * WallacePOS is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3.0 of the License, or (at your option) any later version.
 *
 * WallacePOS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details:
 * <https://www.gnu.org/licenses/lgpl.html>
 *
 * @package    wpos
 * @copyright  Copyright (c) 2014 WallaceIT. (https://wallaceit.com.au)
 * @author     Michael B Wallace <micwallace@gmx.com>
 * @since      Class created 15/1/14 12:01 PM
 */

function WPOS() {

    var initialsetup = false;
    var subscriptionStatus = false;
    var daysRemaining = 0;
    var subscription;
    var isTrial = false;
    this.initApp = function () {
        $.ajaxSetup({
            cache: true
        });
        // check browser features, returns false if the browser does not support required features
        if (!checkAppCompatibility())
            return false;
        // check online status to determine start & load procedure.
        if (checkOnlineStatus()) {
            WPOS.checkCacheUpdate(); // check if application cache is updating or already updated
        } else {
            // check approppriate offline records exist
            if (switchToOffline()) {
                WPOS.initLogin();
            }
        }
    };

    function checkAppCompatibility(){
        // Check local storage: required
        if (!('localStorage' in window && window.localStorage !== null)) {
            swal({
                type: 'error',
                title: 'Oops...',
                text: 'Your browser does not support localStorage required to run the POS terminal.'
              });

            return false;
        }
        // Check application cache: not required to run
        if (window.applicationCache == null){
            swal({
                type: 'error',
                title: 'Oops...',
                text: 'Your browser does not support applicationCache and will not be able to function offline.'
              });
              
        }
        return true;
    }

    var cacheloaded = 1;
    this.checkCacheUpdate = function(){
            if (window.applicationCache == null){
                console.log("Application cache not supported.");
                WPOS.initLogin();
                return;
            }
            var appCache = window.applicationCache;
            // check if cache exists, if the app is loaded for the first time, we don't need to wait for an update
            if (appCache.status == appCache.UNCACHED){
                console.log("Application cache not yet loaded.");
                WPOS.initLogin();
                return;
            }
            // For chrome, the UNCACHED status is never seen, instead listen for the cached event, cache has finished loading the first time
            appCache.addEventListener('cached', function(e) {
                console.log("Cache loaded for the first time, no need for reload.");
                WPOS.initLogin();
            });
            // wait for update to finish: check after applying event listener aswell, we may have missed the event.
            appCache.addEventListener('updateready', function(e) {
                console.log("Appcache update finished, reloading...");
                setLoadingBar(100, "Loading...");
                appCache.swapCache();
                location.reload(true);
            });
            appCache.addEventListener('noupdate', function(e) {
                console.log("No appcache update found");
                WPOS.initLogin();
            });
            appCache.addEventListener('progress', function(e) {
                var loaded = parseInt((100/ e.total)*e.loaded);
                cacheloaded = isNaN(loaded)?(cacheloaded+1):loaded;
                //console.log(cacheloaded);
                setLoadingBar(cacheloaded, "Updating application...");
            });
            appCache.addEventListener('downloading', function(e) {
                console.log("Updating appcache");
                setLoadingBar(1, "Updating application...");
            });
            if (appCache.status == appCache.UPDATEREADY){
                console.log("Appcache update finished, reloading...");
                setLoadingBar(100, "Loading...");
                appCache.swapCache();
                location.reload(true);
            }
    };
    // Check for device UUID & present Login, initial setup is triggered if the device UUID is not present
    this.initLogin = function(){
        showLogin();
        if (getDeviceUUID() == null) {
            // The device has not been setup yet; User will have to login as an admin to setup the device.
            swal({
                type: 'info',
                title: 'Oops...',
                text: 'The device has not been setup yet, please login as an administrator to setup the device.'
              });
            initialsetup = true;
            online = true;
            return false;
        }
        return true;
    };
    // Plugin initiation functions
    this.initPlugins = function(){
        // load keypad if set
        setKeypad(true);
        // load printer plugin
        WPOS.print.loadPrintSettings();
        // deploy scan apps
        deployDefaultScanApp();
        // init eftpos module if available
        if (WPOS.hasOwnProperty('eftpos'))
            WPOS.eftpos.initiate();
    };
    this.initKeypad = function(){
        setKeypad(false);
    };

    this.updateCustTable = function(id, data){
        updateCustTable(id, data);
        // Fill patients dialog for DAA drugs
        var patients = WPOS.getCustTable();
        $('select#patientid.select2-offscreen').find('option').remove().end();
        for (var p in patients){
            $("select#patientid").append('<option data-value="'+p+'" value="'+p+'">'+patients[p].name+'</option>');
        }
        $("#patientid").select2();
    };
    function setKeypad(setcheckbox){
        if (getLocalConfig().keypad == true ){
            WPOS.util.initKeypad();

            if (setcheckbox)
            $("#keypadset").prop("checked", true);
        } else {
            if (setcheckbox)
            $("#keypadset").prop("checked", false);
        }
        // set keypad focus on click
        $(".numpad").on("click", function () {
            $(this).focus().select();
        });
    }
    function deployDefaultScanApp(){
        $.getScript('/assets/js/jquery.scannerdetection.js').done(function(){
            // Init plugin
            $(window).scannerDetection({
                onComplete: function(barcode){
                    // switch to sales tab
                    $("#wrapper").tabs( "option", "active", 0 );
                    WPOS.items.addItemFromStockCode(barcode);
                }
            });
        }).error(function(){
            swal({
                type: 'error',
                title: 'Oops...',
                text: 'Failed to load the scanning plugin.'
              });
              
        });
    }

    // AUTH
    function showLogin(message, lock) {
        $("#loadingdiv").hide();
        $("#logindiv").show();
        $('#loginbutton').removeAttr('disabled', 'disabled');
        setLoadingBar(0, "");
        $('body').css('overflow', 'hidden');

        if (message){
            $("#login-banner-txt").text(message);
            $("#login-banner").show();
        } else {
            $("#login-banner").hide();
        }
        var modal = $('#loginmodal');
        if (lock){
            // session is being locked. set opacity
            modal.css('background-color', "rgba(0,0,0,0.75)");
        } else {
            modal.css('background-color', "#000");
        }
        modal.show();
    }

    function hideLogin(){
        $('#loginmodal').hide();
        $('#loadingdiv').hide();
        $('#logindiv').show();
        $('body').css('overflow', 'auto');
    }

    var session_locked = false;
    this.lockSession = function(){
        $("#username").val(currentuser.username);
        showLogin("The session is locked, login to continue.", true);
        session_locked = true;
    };

    this.userLogin = function () {
        WPOS.util.showLoader();
        var loginbtn = $('#loginbutton');
        // disable login button
        $(loginbtn).prop('disabled', true);
        $(loginbtn).val('Proccessing');
        // get form values
        var userfield = $("#username");
        var passfield = $("#password");
        var username = userfield.val();
        var password = passfield.val();
        // hash password
        password = WPOS.util.SHA256(password);
        // authenticate
        authenticate(username, password, function(result){
          if (result === true) {
            userfield.val('');
            passfield.val('');
            $("#logindiv").hide();
            $("#loadingdiv").show();
            // initiate data download/check
            if (initialsetup) {
              if (isUserAdmin()) {
                initSetup();
              } else {
                swal({
                    type: 'error',
                    title: 'Oops...',
                    text: 'You must login as an administrator for first time setup'
                  });
                  
                showLogin();
              }
            } else {
              if (session_locked){
                stopSocket();
                startSocket();
                session_locked = false;
                hideLogin();
              } else {
                initData(true);
              }
            }
          }
          passfield.val('');
          $(loginbtn).val('Login');
          $(loginbtn).prop('disabled', false);
          WPOS.util.hideLoader();
        });
    };

    this.logout = function () {
       // var answer = confirm("Are you sure you want to logout?");
        swal({
            title: 'Logout',
            text: "Are you sure you want to logout?",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Log Out!'
          }).then(function (result) {
           if (result.value) {
            
            var sales = WPOS.sales.getOfflineSalesNum();
            if (sales>0) {
                answer = confirm("You have offline sales that have not been uploaded to the server.\nWould you like to back them up?");
                if (answer)
                    this.backupOfflineSales();
            }
            WPOS.util.showLoader();
            logout();
            WPOS.util.hideLoader();
                setTimeout(
                    function() 
                    {
                        swal('Logged Out!', 'You have been succesfully logged out.', 'success');
                    }, 200);
                          
            }
          });
    };

    function getSubscription() {
        WPOS.getJsonDataAsync("pos/subscription", function (result) {
          if (result.subscription){
            if (typeof result.subscription === 'string')
              result = JSON.parse(result.subscription);
            else
              result = result.subscription;
            if (result !== false && result !== null)
              if(result.status === 'activated') { // From free trial
                subscriptionStatus = new Date(result.expiryDate).getTime() > new Date().getTime();
                daysRemaining = moment(result.expiryDate).diff(moment(), 'days');
                isTrial = daysRemaining <=14;
              }else { // From server
                daysRemaining = moment(result.expiryDate).diff(moment(), 'days');
                subscriptionStatus = daysRemaining >= 0;
              }
          }else{
            subscriptionStatus = null;
          }
        });
    }

    function logout(){
        WPOS.getJsonDataAsync("logout", function(result){
            if (result !== false){
                stopSocket();
                showLogin();
            }
        });
    }

    function startFeed(){
        /*WPOS.getJsonDataAsync("node/start", function(result){
            if (result !== false){
                startSocket();
            }
        });*/
    }

    function authenticate(user, hashpass, callback) {
        // auth against server if online, offline table if not.
        if (online == true) {
            // send request to server
            WPOS.sendJsonDataAsync("auth", JSON.stringify({username: user, password: hashpass, getsessiontokens:true}), function(response){
                if (response !== false) {
                    // set current user will possibly get passed additional data from server in the future but for now just username and pass is enough
                    setCurrentUser(response);
                    updateAuthTable(response);
                }
                getSubscription();
                if (callback)
                    callback(response!==false);
            });
        } else {
            if (callback)
                callback(offlineAuth(user, hashpass));
        }
    }

    function sessionRenew(){
        // send request to server
        var response = WPOS.sendJsonData("authrenew", JSON.stringify({username:currentuser.username, auth_hash:currentuser.auth_hash}));
        if (response !== false) {
            // set current user will possibly get passed additional data from server in the future but for now just username and pass is enough
            setCurrentUser(response);
            updateAuthTable(response);
            return true;
        } else {
            return false;
        }
    }

    function offlineAuth(username, hashpass) {
        if (localStorage.getItem("wpos_auth") !== null) {
            var jsonauth = $.parseJSON(localStorage.getItem("wpos_auth"));
            if (jsonauth[username] === null || jsonauth[username] === undefined) {
                swal({
                    type: 'error',
                    title: 'Oops...',
                    text: 'Sorry, your credentials are currently not available offline.'
                  });
                  
                return false;
            } else {
                var authentry = jsonauth[username];
                if (authentry.auth_hash == WPOS.util.SHA256(hashpass+authentry.token)) {
                    setCurrentUser(authentry);
                    return true;
                } else {
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'Access denied!'
                      });
                      
                    return false;
                }
            }
        } else {
            swal({
                type: 'error',
                title: 'Oops...',
                text: 'We tried to authenticate you without an internet connection but there are currently no local credentials stored.'
              });
              
            return false;
        }
    }

    this.getCurrentUserId = function () {
        return currentuser.id
    };

    var currentuser;
    // set current user details
    function setCurrentUser(user) {
        currentuser = user;
    }

    function isUserAdmin() {
        return currentuser.isadmin == 1;
    }

    // initiate the setup process
    this.deviceSetup = function () {
        WPOS.util.showLoader();
        var devid = $("#posdevices option:selected").val();
        var devname = $("#newposdevice").val();
        var locid = $("#poslocations option:selected").val();
        var locname = $("#newposlocation").val();
        // check input
        if ((devid == null && devname == null) || (locid == null && locname == null)) {
            swal({
                type: 'error',
                title: 'Oops...',
                text: '"Please select a item from the dropdowns or specify a new name.'
              });
              
        } else {
            // call the setup function
            deviceSetup(devid, devname, locid, locname, function(result){
                if (result) {
                    currentuser = null;
                    initialsetup = false;
                    $("#setupdiv").on('hidden.bs.modal', function () {
                        // do something…
                    });
                    // $("#username").val("admin");
                    // $("#password").val("admin");
                    showLogin();
                    swal({
                        type: 'success',
                        title: 'Success.',
                        text: 'Registration successful, login to start the demo'
                      });
                      
                      
                } else {
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'There was a problem setting up the device, please try again.'
                      });
                      
                }
            });
        }
        WPOS.util.hideLoader();
    };

    function initSetup() {
        $("#loadingbartxt").text("Initializing setup");
        WPOS.util.showLoader();
        // get pos locations and devices and populate select lists
        WPOS.sendJsonDataAsync("multi", JSON.stringify({"devices/get":"", "locations/get":""}), function(data){
            if (data===false)
                return;

            var devices = data['devices/get'];
            var locations = data['locations/get'];

            for (var i in devices) {
                if (devices[i].disabled == 0 && devices[i].type!="kitchen_terminal"){ // do not add disabled devs
                    $("#posdevices").append('<option value="' + devices[i].id + '">' + devices[i].name + ' (' + devices[i].locationname + ')</option>');
                }
            }
            for (i in locations) {
                if (locations[i].disabled == 0){
                    $("#poslocations").append('<option value="' + locations[i].id + '">' + locations[i].name + '</option>');
                }
            }
            WPOS.util.hideLoader();
            // show the setup dialog
            $("#setupdiv").parent().css('z-index', "1000 !important");
            //  $("#setupdiv").dialog("open");
            $("#setupdiv").modal();
        });
    }

    function licenseServer(state) {
      var message = '';
      if(state===0)
        message = 'Check your subscription status!';
      else if (state===1)
        message = 'Check subscription status';
      else
        message = 'Your subscription has expired!';
      swal({
        type: 'info',
        title: message,
        text: 'Enter the email you used to download the software to check for subscription status. If you need more info or help please call support @ 0721733354.',
        input: 'email',
        showCancelButton: true,
        confirmButtonText: 'Check/Renew',
        showLoaderOnConfirm: true,
        preConfirm: (email) => {
          return fetch(`http://biasharapos.com/users/profile/license/subscription?email=${email}`);
        },
        allowOutsideClick: () => !swal.isLoading()
      }).then(json => {
          return json.value.json();
      }).then(function (data) {
          if (data.message !== undefined){
            if (data.message === 'No subscriptions found...!!')
                return false;
            else
                return data.message;
          } else {
            return processSubscriptions(data.subscriptions);
          }
      }).then(function (status) {
        if (typeof status === 'string'){
          swal({
            title: "Error..!",
            text: status,
            type: 'error'
          })
        } else {
          if(status){
            swal({
              type:'success',
              title:"Activated!",
              text: `Subscription activated, from ${moment(subscription.activationDate).format('Do MMM YYYY')} - ${moment(subscription.expiryDate).format('Do MMM YYYY')}, login to continue`
            });
          } else{
            swal({
              type: 'info',
              showConfirmButton: false,
              title: 'Sorry, you have no active subscription.',
              html:'<a class="btn btn-sm btn-success" onClick="openButton();">BUY</a>'
            });
          }
        }
      }).catch(err => {
        if (err) {
          swal("Connection failed", "Error contacting license server, ensure you have internet connection.If error persists call support at 0721733354.", "error");
        } else {
          swal.stopLoading();
          swal.close();
        }
      });
    }

    function processSubscriptions(subscriptions) {
      var found = false;
      for(var i in subscriptions) {
        if (subscriptions[i].status === 1 && !found) {
          subscription = subscriptions[i];
          if (moment(subscriptions[i].expiryDate).diff(moment(), "days") >= 29) {
            found = true;
            WPOS.sendJsonDataAsync("update/subscription", JSON.stringify(subscriptions[i]), function (results) {
              return results;
            });
          }
        }
      }
      return found;
    }

    // get initial data for pos startup.
    function initData(loginloader) {
        getSubscription();
       startFeed();
        if (loginloader){
            $("#loadingprogdiv").show();
            $("#loadingdiv").show();
          setTimeout(()=> {
            if(isTrial){
              if (daysRemaining<=0) daysRemaining = 0;
              swal({
                type: 'info',
                title: 'This is a free trial !!',
                showCancelButton: true,
                confirmButtonText: 'Buy',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                html: '<h5 class="text-danger"><b>'+daysRemaining+' days remaining.</b></h5>Click buy to get a subscription of your choice or cancel to continue with the trial.'
              }).then((confirm)=> {
                if (confirm.value)
                  licenseServer(0)
              });
            } else {
              if (daysRemaining <=5){
                if (daysRemaining<=0) {
                  daysRemaining = 0;
                  licenseServer(2);
                }else {
                  swal({
                    type: 'info',
                    title: 'Your subscription is about to expire!',
                    showCancelButton: true,
                    confirmButtonText: 'Buy',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    html: '<h5 class="text-danger"><b>'+daysRemaining+' days remaining.</b></h5>Click buy to renew your subscription or cancel to continue.'
                  }).then((confirm)=> {
                    if (confirm.value)
                      licenseServer(1)
                  });
                }
              }
            }
          }, 1000);
        }
        if (online) {
            loadOnlineData(1, loginloader);
        } else {
            initOfflineData(loginloader);
        }
    }

    function loadOnlineData(step, loginloader){
        var statusmsg = "The POS is updating data and switching to online mode.";
        switch (step){
            case 1:
                $("#loadingbartxt").text("Loading online resources");
                // get device info and settings
                setLoadingBar(10, "Getting device settings...");
                setStatusBar(4, "Updating device settings...", statusmsg, 0);
                fetchConfigTable(function(data){
                    if (data===false){
                        showLogin();
                        return;
                    }
                    loadOnlineData(2, loginloader);
                });
                break;

            case 2:
                  // get stock
                  setLoadingBar(30, "Getting stock...");
                  setStatusBar(4, "Updating stock...", statusmsg, 0);
                  fetchStockLevel(function(data){
                    if (data===false){
                      showLogin();
                      return;
                    }
                    loadOnlineData(3, loginloader);
                  });

                break;

          case 3:
                // get customers
                setLoadingBar(50, "Getting customer accounts...");
                setStatusBar(4, "Updating customers...", statusmsg, 0);
                fetchCustTable(function(data){
                  if (data===false){
                    showLogin();
                    return;
                  }
                  loadOnlineData(4, loginloader);
                });
                break;
          case 4:
                // get suppliers
                setLoadingBar(60, "Getting suppliers...");
                setStatusBar(4, "Updating suppliers...", statusmsg, 0);
                fetchSuppliersTable(function(data){
                  if (data===false){
                    showLogin();
                    return;
                  }
                  loadOnlineData(5, loginloader);
                });
                break;
          case 5:
                // Get stored items
                setLoadingBar(70, "Getting stored items...");
                setStatusBar(4, "Updating stored items...", statusmsg, 0);
                fetchItemsTable(function(data){
                  if (data===false){
                    showLogin();
                    return;
                  }
                  loadOnlineData(6, loginloader);
                });
                break;
          case 6:
              setLoadingBar(70, "Getting subscription status...");
              if (!subscriptionStatus) {
                stopSocket();
                showLogin("Your subscription is expired.", true);
                return;
              } else {
                loadOnlineData(7, loginloader);
              }
          case 7:
                // get all sales (Will limit to the weeks sales in future)
                setLoadingBar(80, "Getting recent sales...");
                setStatusBar(4, "Updating sales...", statusmsg, 0);
                fetchSalesTable(function(data){
                    if (data===false){
                        showLogin();
                        return;
                    }
                    // start websocket connection
                    startSocket();
                    setStatusBar(1, "Feed server is Online", "The POS is running in online mode.\nThe feed server is connected and receiving realtime updates.", 0);
                    initDataSuccess(loginloader);
                    var offline_num = WPOS.sales.getOfflineSalesNum();
                    if (offline_num>0){
                        $("#backup_btn").show();
                        // check for offline sales on login
                        setTimeout('if (WPOS.sales.uploadOfflineRecords()){ WPOS.setStatusBar(1, "Feed server is online"); }', 2000);
                    } else {
                        $("#backup_btn").hide();
                    }
                });
                break;
        }
    }

    function initOfflineData(loginloader){
        // check records and initiate java objects
        setLoadingBar(50, "Loading offline data...");
        loadConfigTable();
        loadItemsTable();
        loadCustTable();
        loadSalesTable();
        swal({
            type: 'error',
            title: 'Oops...',
            text: 'Your internet connection is not active and Pharmacy Plus POS has started in offline mode.\nSome features are not available in offline mode but you can always make sales and alter transactions that are locally available. \nWhen a connection becomes available the POS will process your transactions on the server.'
          });
          
          
        initDataSuccess(loginloader);
    }

    function initDataSuccess(loginloader){
        if (loginloader){
            setLoadingBar(100, "Massaging the data...");
            $("title").text("Pharmacy Plus POS");
            WPOS.initPlugins();
            populateDeviceInfo();
            setTimeout(hideLogin, 500);
        }
    }

    this.removeDeviceRegistration = function(){
        if (isUserAdmin()){
            var answer = confirm("Are you sure you want to delete this devices registration?\nYou will be logged out and this device will need to be re registered.");
            
            
            
            if (answer){
                // show loader
                WPOS.util.showLoader();
                var regid = WPOS.getConfigTable().registration.id;
                WPOS.sendJsonDataAsync("devices/registrations/delete", '{"id":'+regid+'}', function(result){
                    if (result){
                        removeDeviceUUID();
                        logout();
                    }
                    // hide loader
                    WPOS.util.hideLoader();
                });
            }
            return;
        }
        swal({
            type: 'error',
            title: 'Oops...',
            text: 'Please login as an administrator to use this feature'
          });
          
    };

    this.resetLocalConfig = function(){
        if (isUserAdmin()){
            //var answer = confirm("Are you sure you want to restore local settings to their defaults?\n");
            var answer;

            swal({
                title: 'Are you sure?\n',
                text: "Your local settings will be reset to their defaults? You won't be able to revert this!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Restore!'
              }).then(function (answer){
                if (answer.value) {
                  swal(
                    'Restored!',
                    'Your local settings have been restored.',
                    'success'
                  )
                }
              });

              if (answer){
                localStorage.removeItem("wpos_lconfig");
                WPOS.print.loadPrintSettings();
                setKeypad(true);
         
            }
            return;
        }
        swal({
            type: 'error',
            title: 'Oops...',
            text: 'Please login as an administrator to use this feature'
          });
          
    };

    this.clearLocalData = function(){
        if (isUserAdmin()){
           // var answer = confirm("Are you sure you want to clear all local data?\nThis removes all locally stored data except device registration key.\nOffline Sales will be deleted.");
            var answer;
            swal({
                title: 'Are you sure?\n',
                text: 'This action will remove all locally stored data except device registration key.\nOffline Sales will also be deleted.',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Clear!'
              }).then(function (answer){
                if (answer.value) {
                  swal(
                    'Cleared!',
                    'Your stored data has been cleared.',
                    'success'
                  )
                }
              });

            if (answer){
                localStorage.removeItem("wpos_auth");
                localStorage.removeItem("wpos_config");
                localStorage.removeItem("wpos_csales");
                localStorage.removeItem("wpos_osales");
                localStorage.removeItem("wpos_items");
                localStorage.removeItem("wpos_customers");
                localStorage.removeItem("wpos_lconfig");
            }
            return;
        }
        swal({
            type: 'error',
            title: 'Oops...',
            text: 'Please login as an administrator to use this feature'
          });
    };

    this.refreshRemoteData = function(){
       // var answer = confirm("Are you sure you want to reload data from the server?");
        swal({
            title: 'Reload Data',
            text: "Are you sure you want to reload data from the server?",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Reload it!'
          }).then(function (result) {
           if (result.value) {
            
            loadOnlineData(1, false);
                            setTimeout(
                    function() 
                    {
                        swal('Reloaded!', 'Your data has been reloaded from the server.', 'success');
                    }, 200);
                          
            }
          });
       /* if (answer){
            loadOnlineData(1, false);
        }*/
    };

    this.refreshData = function(step, loginloader) {
        /*if(step && (loginloader || loginloader == false))
            loadOnlineData(step, loginloader);
        if(step)
            loadOnlineData(step, false);
        if(loginloader)
            loadOnlineData(1, loginloader);
        if(!step && false)*/
            loadOnlineData(1, false);
    };

    this.backupOfflineSales = function(){
        var offline_sales = localStorage.getItem('wpos_osales');

        var a = document.createElement('a');
        var blob = new Blob([offline_sales], {'type':"application/octet-stream"});
        window.URL = window.URL || window.webkitURL;
        a.href = window.URL.createObjectURL(blob);
        var date = new Date();
        var day = date.getDate();
        if (day.length==1) day = '0' + day;
        a.download = "wpos_offline_sales_"+date.getFullYear()+"-"+(date.getMonth()+1)+"-"+day+"_"+date.getHours()+"-"+date.getMinutes()+".json";
        document.body.appendChild(a);
        a.click();
        a.remove();
    };

    this.removeOfflineSales = function() {
        localStorage.removeItem('wpos_osales');
        swal("Done", "Restart the app to reflect changes");
    };

    function populateDeviceInfo(){
        var config = WPOS.getConfigTable();
        $(".device_id").text(config.deviceid);
        $(".device_name").text(config.devicename);
        $(".location_id").text(config.locationid);
        $(".location_name").text(config.locationname);
        $(".devicereg_id").text(config.registration.id);
        $(".devicereg_uuid").text(config.registration.uuid);
        $(".devicereg_dt").text(config.registration.dt);
        $(".biz_name").text(config.general.bizname);
    }

    function setLoadingBar(progress, status) {
        var loadingprog = $("#loadingprog");
        var loadingstat = $("#loadingstat");
        $(loadingstat).text(status);
        $(loadingprog).css("width", progress + "%");
    }

    /**
     * Update the pos status text and icon
     * @param statusType (1=Online, 2=Uploading, 3=Offline, 4=Downloading)
     * @param text
     * @param tooltip
     * @param timeout
     */
    this.setStatusBar = function(statusType, text, tooltip, timeout){
        setStatusBar(statusType, text, tooltip, timeout);
    };

    var defaultStatus = {type:1, text:"", tooltip:""};
    var statusTimer = null;

    function setDefaultStatus(statusType, text, tooltip){
        defaultStatus.type = statusType;
        defaultStatus.text = text;
        defaultStatus.tooltip = tooltip;
    }

    function setStatusBar(statusType, text, tooltip, timeout){
        if (timeout===0){
            setDefaultStatus(statusType, text, tooltip);
        } else if (timeout > 0 && statusTimer!=null){
            clearTimeout(statusTimer);
        }

        var staticon = $("#wposstaticon");
        var statimg = $("#wposstaticon i");
        switch (statusType){
            // Online icon
            case 1: $(staticon).attr("class", "badge badge-success");
                $(statimg).attr("class", "icon-ok");
                break;
            // Upload icon
            case 2: $(staticon).attr("class", "badge badge-info");
                $(statimg).attr("class", "icon-cloud-upload");
                break;
            // Offline icon
            case 3: $(staticon).attr("class", "badge badge-warning");
                $(statimg).attr("class", "icon-exclamation");
                break;
            // Download icon
            case 4: $(staticon).attr("class", "badge badge-info");
                $(statimg).attr("class", "icon-cloud-download");
                break;
            // Feed server disconnected
            case 5: $(staticon).attr("class", "badge badge-warning");
                $(statimg).attr("class", "icon-ok");
        }
        $("#wposstattxt").text(text);
        $("#wposstat").attr("title", tooltip);

        if (timeout > 0){
            statusTimer = setTimeout(resetStatusBar, timeout);
        }
    }

    // reset status bar to the current default status
    function resetStatusBar(){
        clearTimeout(statusTimer);
        statusTimer = null;
        setStatusBar(defaultStatus.type, defaultStatus.text, defaultStatus.tooltip);
    }

    var online = false;

    this.isOnline = function () {
        return online;
    };

    function checkOnlineStatus() {

        try {
            var res = $.ajax({
            timeout : 3000,
            url     : "/api/hello",
            type    : "GET",
            cache   : true,
            dataType: "text",
            async   : false
            }).status;
            online = res == 200;
        } catch (ex){
            online = false;
        }
        return online;
    }

    // OFFLINE MODE FUNCTIONS
    function canDoOffline() {
        if (getDeviceUUID()!==null) { // can't go offline if device hasn't been setup
            // check for auth table
            if (localStorage.getItem("wpos_auth") == null) {
                return false;
            }
            // check for machine settings etc.
            if (localStorage.getItem("wpos_config") == null) {
                return false;
            }
            return localStorage.getItem("wpos_items") != null;
        }
        return false;
    }

    var checktimer;

    this.switchToOffline = function(){
        return switchToOffline();
    };

    function switchToOffline() {
        if (canDoOffline() === true) {
            // set js indicator: important
            online = false;
            setStatusBar(3, "Feed server is Offline", "The POS is offine and will store sale data locally until a connection becomes available.", 0);
            // start online check routine
            checktimer = setInterval(doOnlineCheck, 60000);
            if (WPOS.sales.getOfflineSalesNum()>0)
                $(".backup_btn").show();
                $(".remove_btn").show();
            return true;
        } else {
            // display error notice
            swal({
                type: 'error',
                title: 'Oops...',
                text: 'There was an error connecting to the webserver & files needed to run offline are not present :( \nPlease check your connection and try again.'
              });
              
            showLogin();
            setLoadingBar(100, "Error switching to offine mode");
            return false;
        }
    }

    function doOnlineCheck() {
        if (checkOnlineStatus() === true) {
            clearInterval(checktimer);
            switchToOnline();
        }
    }

    function switchToOnline() {
        // upload offline sales
        if (WPOS.sales.uploadOfflineRecords()){
            // set js and ui indicators
            online = true;
            // load fresh data
            initData(false);
            // initData();
            setStatusBar(1, "Feed server is Online", "The POS is running in online mode.\nThe feed server is connected and receiving realtime updates.", 0);
        }
    }

    // GLOBAL COM FUNCTIONS
    this.sendJsonData = function (action, data) {
        // send request to server
        try {
        var response = $.ajax({
            url     : "/api/"+action,
            type    : "POST",
            data    : {data: data},
            dataType: "text",
            timeout : 10000,
            cache   : false,
            async   : false
        });
        if (response.status == "200") {
            var json = $.parseJSON(response.responseText);
            if (json == null) {
                swal({
                    type: 'error',
                    title: 'Oops...',
                    text: 'Error: The response that was returned from the server could not be parsed!'
                  });
                  
                return false;
            }
            var errCode = json.errorCode;
            var err = json.error;
            if (err == "OK") {
                // echo warning if set
                if (json.hasOwnProperty('warning')){
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: json.warning
                      });
                      
                }
                return json.data;
            } else {
                if (errCode == "auth") {
                    if (sessionRenew()) {
                        // try again after authenticating
                        return WPOS.sendJsonData(action, data);
                    } else {
                        return false;
                    }
                } else {
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: err
                      });
                      
                    return false;
                }
            }
        } else {
            switchToOffline();
            swal({
                type: 'error',
                title: 'Oops...',
                text: 'There was an error connecting to the server: \n"+response.statusText+", \n switching to offline mode'
              });
              
            return false;
        }
        } catch (ex) {
            switchToOffline();
            swal({
                type: 'error',
                title: 'Oops...',
                text: 'There was an error sending data, switching to offline mode.\nException: '+ex.message
              });
              
            return false;
        }
    };

    this.sendJsonDataAsync = function (action, data, callback) {
        // send request to server
        try {
            $.ajax({
                url     : "/api/"+action,
                type    : "POST",
                data    : {data: data},
                dataType: "json",
                timeout : 10000,
                cache   : false,
                success : function(json){
                    var errCode = json.errorCode;
                    var err = json.error;
                    if (err == "OK") {
                        // echo warning if set
                        if (json.hasOwnProperty('warning')){
                            swal({
                                type: 'error',
                                title: 'Oops...',
                                text: json.warning
                              });
                              
                              
                        }
                        callback(json.data);
                    } else {
                        if (errCode == "auth") {
                            if (sessionRenew()) {
                                // try again after authenticating
                                var result = WPOS.sendJsonData(action, data);
                                callback(result);
                            } else {
                                callback(false);
                            }
                        } else {
                            swal({
                                type: 'error',
                                title: 'Oops...',
                                text: err
                              });
                              
                            callback(false);
                        }
                    }
                },
                error   : function(jqXHR, status, error){
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: error
                      });
                    callback(false);
                }
            });
        } catch (ex) {
            swal({
                type: 'error',
                title: 'Oops...',
                text: 'Exception: '+ex.message
              });
              
            callback(false);
        }
    };

    this.getJsonDataAsync = function (action, callback) {
        // send request to server
        try {
            $.ajax({
                url     : "/api/"+action,
                type    : "GET",
                dataType: "json",
                timeout : 10000,
                cache   : false,
                success : function(json){
                    var errCode = json.errorCode;
                    var err = json.error;
                    if (err == "OK") {
                        // echo warning if set
                        if (json.hasOwnProperty('warning')){
                            swal({
                                type: 'error',
                                title: 'Oops...',
                                text: json.warning
                              });
                              
                        }
                        if (callback)
                            callback(json.data);
                    } else {
                        if (errCode == "auth") {
                            if (sessionRenew()) {
                                // try again after authenticating
                                var result = WPOS.sendJsonData(action, data);
                                if (result){
                                    if (callback)
                                        callback(result);
                                    return;
                                }
                            }
                        }
                        swal({
                            type: 'error',
                            title: 'Oops...',
                            text: err
                          });
                          
                          
                        if (callback)
                            callback(false);
                    }
                },
                error   : function(jqXHR, status, error){
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: error
                      });
                      
                      
                    if (callback)
                        callback(false);
                }
            });
        } catch (ex) {
            swal({
                type: 'error',
                title: 'Oops...',
                text: 'Exception: '+ex.message
              });
              
              
            if (callback)
                callback(false);
        }
    };

    // AUTHENTICATION & USER SETTINGS
    /**
     * Update the offline authentication table using the json object provided. This it returned on successful login.
     * @param {object} jsonobj ; user record returned by authentication
     */
    function updateAuthTable(jsonobj) {
        var jsonauth;
        if (localStorage.getItem("wpos_auth") !== null) {
            jsonauth = $.parseJSON(localStorage.getItem("wpos_auth"));
            jsonauth[jsonobj.username.toString()] = jsonobj;
        } else {
            jsonauth = { };
            jsonauth[jsonobj.username.toString()] = jsonobj;
        }
        localStorage.setItem("wpos_auth", JSON.stringify(jsonauth));
    }

    // DEVICE SETTINGS AND INFO
    var configtable;

    this.getConfigTable = function () {
        if (configtable == null) {
            loadConfigTable();
        }
        return configtable;
    };

    this.refreshConfigTable = function () {
        fetchConfigTable();
    };

    this.isOrderTerminal = function () {
        if (configtable == null) {
            loadConfigTable();
        }
        return configtable.hasOwnProperty('deviceconfig') && configtable.deviceconfig.type == "order_register";
    };
    /**
     * Fetch device settings from the server using UUID
     * @return boolean
     */
    function fetchConfigTable(callback) {
        var data = {};
        data.uuid = getDeviceUUID();
        return WPOS.sendJsonDataAsync("config/get", JSON.stringify(data), function(data){
            if (data) {
                if (data=="removed" || data=="disabled"){ // return false if dev is disabled
                    if (data=="removed")
                        removeDeviceUUID();
                    if (callback){
                        callback(false);
                        return;
                    }
                } else {
                    configtable = data;
                    localStorage.setItem("wpos_config", JSON.stringify(data));
                    setAppCustomization();
                }
            }
            if (callback)
                callback(data);
        });
    }

    function loadConfigTable() {
        var data = localStorage.getItem("wpos_config");
        if (data != null) {
            configtable = JSON.parse(data);
            return true;
        }
        configtable = {};
        return false;
    }

    function updateConfig(key, data){
        console.log("Processing config ("+key+") update");
        //console.log(data);

        if (key=='item_categories')
            return updateCategory(data);

        if (key=="deviceconfig"){
            if (data.id==configtable.deviceid) {
                if (data.hasOwnProperty('a') && (data.a == "removed" || data.a == "disabled")) {
                    // device removed
                    if (data.a == "removed")
                        removeDeviceUUID();
                    logout();
                   
                    swal({
                        type: 'error',
                        title: 'Oops...',
                        text: 'This device has been " + data.a + " by the administrator,\ncontact your device administrator for help.'
                      });
                      
                    return;
                }
                // update root level config values
                configtable.devicename = data.name;
                configtable.locationname = data.locationname;
                populateDeviceInfo();
            } else {
                if (data.data.hasOwnProperty('a')){
                    if (data.data.a=="removed")
                        delete configtable.devices[data.id];
                } else {
                    configtable.devices[data.id] = data;
                    configtable.locations[data.locationid] = {name: data.locationname};
                }
                return;
            }
        }

        configtable[key] = data; // write to current data
        localStorage.setItem("wpos_config", JSON.stringify(configtable));
        setAppCustomization();
    }

    function updateCategory(value){
        if (typeof value === 'object'){
            configtable.item_categories[value.id] = value;
        } else {
            if (typeof value === 'string') {
                var ids = value.split(",");
                for (var i=0; i<ids.length; i++){
                    delete configtable.item_categories[ids[i]];
                }
            } else {
                delete configtable.item_categories[value];
            }
        }
        WPOS.items.generateItemGridCategories();
        localStorage.setItem("wpos_config", JSON.stringify(configtable));
    }

    function setAppCustomization(){
        // initialize terminal mode (kitchen order views)
        if (configtable.hasOwnProperty('deviceconfig') && configtable.deviceconfig.type == "order_register") {
            $(".order_terminal_options").show();
            WPOS.sales.resetSalesForm();
        } else {
            $(".order_terminal_options").hide();
            $("#itemtable .order_row").remove(); // clears order row already in html
        }
        // setup checkout watermark
        var url = WPOS.getConfigTable().general.bizlogo;
        $("#watermark").css("background-image", "url('"+url+"')");
    }

    this.getTaxTable = function () {
        if (configtable == null) {
            loadConfigTable();
        }
        return configtable.tax;
    };

    // Local Config
    this.setLocalConfigValue = function(key, value){
        setLocalConfigValue(key, value);
    };

    this.getLocalConfig = function(){
        return getLocalConfig();
    };

    function getLocalConfig(){
        var lconfig = localStorage.getItem("wpos_lconfig");
        if (lconfig==null || lconfig==undefined){
            // put default config here.
            var defcon = {
                keypad: true,
                eftpos:{
                    enabled: false,
                    receipts:true,
                    provider: 'tyro',
                    merchrec:'ask',
                    custrec:'ask'
                }
            };
            updateLocalConfig(defcon);
            return defcon;
        }
        return JSON.parse(lconfig);
    }

    function setLocalConfigValue(key, value){
        var data = localStorage.getItem("wpos_lconfig");
        if (data==null){
            data = {};
        } else {
            data = JSON.parse(data);
        }
        data[key] = value;
        updateLocalConfig(data);
        if (key == "keypad"){
            setKeypad(false);
        }
    }

    function updateLocalConfig(configobj){
        localStorage.setItem("wpos_lconfig", JSON.stringify(configobj));
    }

    /**
     * This function sets up the
     * @param {int} devid ; if not null, the newname var is ignored and the new uuid is merged with the device specified by devid.
     * @param {int} newdevname ; A new device name, if specified the
     * @param {int} locid ; if not null, the newlocname field is ignored and blah blah blah....
     * @param {int} newlocname ; if not null, the newlocname field is ignored and blah blah blah....
     * @returns {boolean}
     */
    function deviceSetup(devid, newdevname, locid, newlocname, callback) {
        var data = {};
        data.uuid = setDeviceUUID(false);
        if (devid === undefined || devid === null || devid === "") {
            data.devicename = newdevname;
        } else {
            data.deviceid = devid;
        }
        if (locid === undefined || locid === null || locid == "") {
            data.locationname = newlocname;
        } else {
            data.locationid = locid;
        }
        if (subscriptionStatus === null) {
          data.subscriptionStatus = false;
        }
        WPOS.sendJsonDataAsync("devices/setup", JSON.stringify(data), function(configobj){
            if (configobj !== false) {
                localStorage.setItem("wpos_config", JSON.stringify(configobj));
                configtable = configobj;
            } else {
                removeDeviceUUID(true);
            }
            if (callback)
                callback(configobj !== false);
        });
    }

    /**
     * Returns the current devices UUID
     * @returns {String, Null} String if set, null if not
     */
    function getDeviceUUID() {
        // return the devices uuid; if null, the device has not been setup or local storage was cleared
        return localStorage.getItem("wpos_devuuid");
    }

    function removeDeviceUUID() {
        initialsetup = true;
        localStorage.removeItem("wpos_devuuid");
    }

    /**
     * Creates or clears device UUID and updates in local storage
     * @returns String uuid
     */
    function setDeviceUUID() {
        // generate a SHA UUID using datestamp and rand for entropy and return the result
        var date = new Date().getTime();
        var uuid = WPOS.util.SHA256((date * Math.random()).toString());
        localStorage.setItem("wpos_devuuid", uuid);
        return uuid;
    }

    // RECENT SALES
    var salestable;

    this.getSalesTable = function () {
        if (salestable == null) {
            loadSalesTable();
        }
        return salestable;
    };

    this.updateSalesTable = function (ref, saleobj) {
        salestable[ref] = saleobj;
    };

    this.removeFromSalesTable = function (ref){
        delete salestable[ref];
    };

    this.loadSales = function () {
        fetchSalesTable(function () {
            WPOS.trans.setupTransactionView();
        });
    };

    function fetchSalesTable(callback) {
        return WPOS.sendJsonDataAsync("sales/get", JSON.stringify({deviceid: configtable.deviceid}), function(data){
            if (data) {
                salestable = data;
                localStorage.setItem("wpos_csales", JSON.stringify(data));
            }
            if (callback)
                callback(data);
        });
    }

    // loads from local storage
    function loadSalesTable() {
        var data = localStorage.getItem("wpos_csales");
        if (data !== null) {
            salestable = JSON.parse(data);
            return true;
        }
        return false;
    }

    // adds/updates a record in the current table
    function updateSalesTable(saleobject) {
        // delete the sale if ref supplied
        if (typeof saleobject === 'object'){
            salestable[saleobject.ref] = saleobject;
        } else {
            delete salestable[saleobject];
        }
        localStorage.setItem("wpos_csales", JSON.stringify(salestable));
    };

    // STORED ITEMS
    var itemtable;
    var stocktable;
    var stockindex;
    var categoryindex;

    this.getItemsTable = function () {
        if (itemtable == null) {
            loadItemsTable();
        }
        return itemtable;
    };

    this.getStockLevel = function() {
        loadStockTable();
        return stocktable;
    };

    this.getSuppliers = function() {
         if (supplierstable == null) {
           loadSuppliersTable();
         }
      return supplierstable;
    };

    this.getStockIndex = function () {
        if (stockindex === undefined || stockindex === null) {
            if (itemtable == null) {
                loadItemsTable(); // also generate stock index
            } else {
                generateItemIndex();
            }
        }
        return stockindex;
    };

    this.getCategoryIndex = function () {
        if (categoryindex === undefined || categoryindex === null) {
            if (itemtable == null) {
                loadItemsTable(); // also generate stock index
            } else {
                generateItemIndex();
            }
        }
        return categoryindex;
    };


    // fetches from server
    function fetchItemsTable(callback) {
        return WPOS.getJsonDataAsync("items/get", function(data){
            if (data) {
                itemtable = data;
                localStorage.setItem("wpos_items", JSON.stringify(data));
                generateItemIndex();
                WPOS.items.generateItemGridCategories();
            }
            if (callback)
                callback(data);
        });
    }

    function fetchStockLevel(callback) {
       return WPOS.getJsonDataAsync("stock/get", function(data){
            if (data) {
                localStorage.setItem("stock_items", JSON.stringify(data));
                WPOS.items.setStock(data);
            }
            if (callback)
                callback(data);
        });
    };

    function fetchSuppliersTable(callback) {
        return WPOS.getJsonDataAsync("suppliers/get", function(data){
          if (data) {
            supplierstable = data;
            localStorage.setItem("wpos_suppliers", JSON.stringify(data));
          }
          if (callback)
            callback(data);
        });
    }

    this.fetchAccountingStats = function(callback) {
       return WPOS.getJsonDataAsync("stats/accounting", function (data) {
           console.log(data)
           if (callback)
               callback(data);
       });
    }

    function generateItemIndex() {
        stockindex = {};
        categoryindex = {};
        for (var key in itemtable) {
            stockindex[itemtable[key].code] = key;

            var categoryid = itemtable[key].hasOwnProperty('categoryid')?itemtable[key].categoryid:0;
            if (categoryindex.hasOwnProperty(categoryid)){
                categoryindex[categoryid].push(key);
            } else {
                categoryindex[categoryid] = [key];
            }
        }
    }

    // loads from local storage
    function loadItemsTable() {
        var data = localStorage.getItem("wpos_items");
        if (data != null) {
            itemtable = JSON.parse(data);
            // generate the stock index as well.
            generateItemIndex();
            WPOS.items.setStock(data);
            WPOS.items.generateItemGridCategories();
            return true;
        }
        return false;
    }

    function loadStockTable() {
        var data = localStorage.getItem("stock_items");
        if (data != null) {
            stocktable = JSON.parse(data);
            WPOS.items.stock = {};
            WPOS.items.stock = data;
            return true;
        }
        return false;
    }

      function loadSuppliersTable() {
        var data = localStorage.getItem("wpos_suppliers");
        if (data !== null) {
          supplierstable = JSON.parse(data);
          return true;
        }
        return false;
      };
    // adds/edits a record to the current table
    function updateItemsTable(itemobject) {
        // delete the sale if id/ref supplied
        if (typeof itemobject === 'object'){
            itemtable[itemobject.id] = itemobject;
        } else {
            if (typeof itemobject === 'string') {
                var ids = itemobject.split(",");
                for (var i=0; i<ids.length; i++){
                    delete itemtable[ids[i]];
                }
            } else {
                delete itemtable[itemobject];
            }
        }
        localStorage.setItem("wpos_items", JSON.stringify(itemtable));
        generateItemIndex();
        WPOS.items.generateItemGridCategories();
    }

    // CUSTOMERS
    var custtable;
    var custindex = [];
    this.getCustTable = function () {
        if (custtable == null) {
            loadCustTable();
        }
        return custtable;
    };
    this.getCustId = function(email){
        if (custindex.hasOwnProperty(email)){
            return custindex[email];
        }
        return false;
    };
    // fetches from server
    function fetchCustTable(callback) {
        return WPOS.getJsonDataAsync("customers/get", function(data){
            if (data) {
                custtable = data;
                localStorage.setItem("wpos_customers", JSON.stringify(data));
                generateCustomerIndex();
            }
            if (callback)
                callback(data);
        });
    }

    // loads from local storage
    function loadCustTable() {
        var data = localStorage.getItem("wpos_customers");
        if (data != null) {
            custtable = JSON.parse(data);
            generateCustomerIndex();
            return true;
        }
        return false;
    }

    function generateCustomerIndex(){
        custindex = [];
        for (var i in custtable){
            custindex[custtable[i].email] = custtable[i].id;
        }
    }


    // adds a record to the current table
    function updateCustTable(id, data) {
        if (typeof data === 'object'){
            custtable[data.id] = data;
            // add/update index
            // custindex[data.email] = data.id;
        } else {
            delete custtable[data];
            for (var i in custindex){
                if (custindex.hasOwnProperty(i) && custindex[i]==data) delete custindex[i];
            }
        }
        // save to local store
        localStorage.setItem("wpos_customers", JSON.stringify(custtable));
    }
    // Websocket updates & commands
    var socket = null;
    var socketon = false;
    var authretry = false;
    function startSocket(){
        return;
        if (socket == null){
            var proxy = WPOS.getConfigTable().general.feedserver_proxy;
            var port = WPOS.getConfigTable().general.feedserver_port;
            var socketPath = window.location.protocol+'//'+window.location.hostname+(proxy==false ? ':'+port : (window.location.port ? ':'+window.location.port : ''));
            socket = io.connect(socketPath);
            socket.on('connection', onSocketConnect);
            socket.on('reconnect', onSocketConnect);
            socket.on('connect_error', socketError);
            socket.on('reconnect_error', socketError);
            socket.on('error', socketError);

            socket.on('updates', function (data) {
                switch (data.a){
                    case "item":
                        updateItemsTable(data.data);
                        break;

                    case "sale":
                        // alert(data.data)
                        updateSalesTable(data.data);
                        break;

                    case "customer":
                        updateCustTable(data.data);
                        break;

                    case "config":
                        updateConfig(data.type, data.data);
                        break;

                    case "regreq":
                        socket.emit('reg', {deviceid: configtable.deviceid, username: currentuser.username});
                        break;

                    case "msg":
                        swal({
                            type: 'info',
                            title: 'You have a new message.',
                            text: data.data
                          });
                          
                        break;

                    case "reset":
                        resetTerminalRequest();
                        break;

                    case "kitchenack":
                        WPOS.orders.kitchenTerminalAcknowledge(data.data);
                        break;

                    case "error":
                        if (!authretry && data.data.hasOwnProperty('code') && data.data.code=="auth"){
                            authretry = true;
                            stopSocket();
                            WPOS.getJsonDataAsync('auth/websocket', function(result){
                                if (result===true)
                                    startSocket();
                            });
                            return;
                        }

                        swal({
                            type: 'success',
                            title: 'Oops...',
                            text: data.data
                          });
                          

                        break;
                }
                var statustypes = ['item', 'sale', 'customer', 'config', 'kitchenack'];
                if (statustypes.indexOf(data.a) > -1) {
                    var statustxt = data.a=="kitchenack" ? "Kitchen Order Acknowledged" : "Receiving "+ data.a + " update";
                    var statusmsg = data.a=="kitchenack" ? "The POS has received an acknowledgement that the last order was received in the kitchen" : "The POS has received updated "+ data.a + " data from the server";
                    setStatusBar(4, statustxt, statusmsg, 5000);
                }
                //alert(data.a);
            });
        } else {
            socket.connect();
        }
    }

    function onSocketConnect(){
        socketon = true;
        if (WPOS.isOnline() && defaultStatus.type != 1){
            setStatusBar(1, "Feed server is Online", "The POS is running in online mode.\nThe feed server is connected and receiving realtime updates.", 0);
        }
    }

    function socketError(){
        if (WPOS.isOnline())
            setStatusBar(5, "Update Feed Offline", "The POS is running in online mode.\nThe feed server is disconnected and this terminal will not receive realtime updates.", 0);
        socketon = false;
        authretry = false;
    }

    function stopSocket(){
        if (socket!=null){
            socketon = false;
            authretry = false;
            socket.disconnect();
            socket = null;
        }
    }

    window.onbeforeunload = function(){
        socketon = false;
    };

    // Reset terminal
    function resetTerminalRequest(){
        // Set timer
        var reset_timer = setTimeout("window.location.reload(true);", 10000);
        var reset_interval = setInterval('var r=$("#resettimeval"); r.text(r.text()-1);', 1000);
        $("#resetdialog").removeClass('hide').dialog({
            width : 'auto',
            maxWidth        : 370,
            modal        : true,
            closeOnEscape: false,
            autoOpen     : true,
            create: function( event, ui ) {
                // Set maxWidth
                $(this).css("maxWidth", "370px");
            },
            buttons: [
                {
                    html: "<i class='icon-check bigger-110'></i>&nbsp; Ok",
                    "class": "btn btn-success btn-xs",
                    click: function () {
                        window.location.reload(true);
                    }
                },
                {
                    html: "<i class='icon-remove bigger-110'></i>&nbsp; Cancel",
                    "class": "btn btn-xs",
                    click: function () {
                        clearTimeout(reset_timer);
                        clearInterval(reset_interval);
                        $("#resetdialog").dialog('close');
                        $("#resettimeval").text(10);
                    }
                }
            ]
        });
    }

    // Contructor code
    // load WPOS Objects
    this.items = new WPOSItems();
    this.sales = new WPOSSales();
    this.trans = new WPOSTransactions();
    this.reports = new WPOSReports();
    this.print = new WPOSPrint();
    this.orders = new WPOSOrders();
    this.util = new WPOSUtil();

    if (typeof(WPOSEftpos) === 'function')
        this.eftpos = new WPOSEftpos();
}
// UI widget functions & initialization
var toggleItemBox;
$(function () {
    // initiate core object
    WPOS = new WPOS();
    // initiate startup routine
    WPOS.initApp();

    $("#wrapper").tabs();

    $("#paymentsdiv").dialog({
        maxWidth : 5000,
        width : 'auto',
        modal   : true,
        autoOpen: false,
        open    : function (event, ui) {
        },
        close   : function (event, ui) {
        },
        create: function( event, ui ) {
            // Set maxWidth
            $(this).css("maxWidth", "500px");
            $(this).css("minWidth", "500px");
        }
    });

    $("#creditpaymentsdiv").dialog({
        maxWidth : 500,
        width : 'auto',
        modal   : true,
        autoOpen: false,
        open    : function (event, ui) {
        },
        close   : function (event, ui) {
        },
        create: function( event, ui ) {
            // Set maxWidth
            $(this).css("maxWidth", "500px");
            $(this).css("minWidth", "500px");
        }
    });

    $("#transactiondiv").dialog({
        width   : 'auto',
        maxWidth: 900,
        modal   : true,
        autoOpen: false,
        title_html: true,
        open    : function (event, ui) {
        },
        close   : function (event, ui) {
        },
        create: function( event, ui ) {
            // Set maxWidth
            $(this).css("maxWidth", "900px");
        }
    });

    $("setupdiv").dialog({
        width        : 200,
        maxWidth     : 200,
        modal        : true,
        closeOnEscape: false,
        autoOpen     : false,
        dialogClass: 'setup-dialog',
        open         : function (event, ui) {
            $('#setupdiv').hide();
        },
        close        : function (event, ui) {
            $('#setupdiv').show();
        },
        create: function( event, ui ) {
            // Set maxWidth
            $(this).css("maxWidth", "500px");
        }
    });

    $("#formdiv").dialog({
        width : 'auto',
        maxWidth     : 370,
        stack        : true,
        modal        : true,
        closeOnEscape: false,
        autoOpen     : false,
        open         : function (event, ui) {
        },
        close        : function (event, ui) {
        },
        create: function( event, ui ) {
            // Set maxWidth
            $(this).css("maxWidth", "370px");
        }
    });

    $("#voiddiv").dialog({
        width : 'auto',
        maxWidth        : 370,
        appendTo     : "#transactiondiv",
        modal        : true,
        closeOnEscape: false,
        autoOpen     : false,
        open         : function (event, ui) {
        },
        close        : function (event, ui) {
        },
        create: function( event, ui ) {
            // Set maxWidth
            $(this).css("maxWidth", "370px");
        }
    });

    $("#custdiv").dialog({
        width : 'auto',
        maxWidth        : 370,
        modal        : true,
        closeOnEscape: false,
        autoOpen     : false,
        open         : function (event, ui) {
        },
        close        : function (event, ui) {
        },
        create: function( event, ui ) {
            // Set maxWidth
            $(this).css("maxWidth", "370px");
        }
    });

    $("#patientInfoDialog" ).removeClass('hide').dialog({
        resizable: false,
        maxWidth: 800,
        width: 'auto',
        modal: true,
        autoOpen: false,
        title: "Add Patient",
        title_html: true,

        buttons: [
            {
                html: "<i class='icon-edit bigger-110'></i>&nbsp; Save",
                "class" : "btn btn-success btn-xs",
                click: function() {
                    // addInvoice();
                    $( this ).dialog( "close" );
                    swal({
                        type: 'info',
                        title: 'Patient added..!!',
                        text: 'You have added '+ WPOS.getCustTable()[parseInt($('#patientid').val())].name
                    });
                }
            },
            {
                html: "<i class='icon-remove bigger-110'></i>&nbsp; Cancel",
                "class" : "btn btn-xs",
                click: function() {
                    $( this ).dialog( "close" );
                }
            }
        ],
        create: function( event, ui ) {
            // Set maxWidth
            $(this).css("maxWidth", "800px");
        }
    });

    $( "#addcustdialog" ).removeClass('hide').dialog({
        resizable: false,
        width: 'auto',
        modal: true,
        autoOpen: false,
        title: "New Patient",
        title_html: true,
        buttons: [
            {
                html: "<i class='icon-save bigger-110'></i>&nbsp; Save",
                "class" : "btn btn-success btn-xs",
                click: function() {
                    WPOS.sales.saveCustomer();
                }
            }
            ,
            {
                html: "<i class='icon-remove bigger-110'></i>&nbsp; Cancel",
                "class" : "btn btn-xs",
                click: function() {
                    $( this ).dialog( "close" );
                }
            }
        ],
        create: function( event, ui ) {
            // Set maxWidth
            $(this).css("maxWidth", "400px");
        }
    });

    // Fill patients dialog for DAA drugs
    var patients = WPOS.getCustTable();
    $('select#patientid.select2-offscreen').find('option').remove().end();
    for (var p in patients){
        $("select#patientid").append('<option data-value="'+p+'" value="'+p+'">'+patients[p].name+'</option>');
    }
    $("#patientid").select2();
    // item box
    var ibox = $("#ibox");
    var iboxhandle = $("#iboxhandle");
    var iboxopen = false;
    toggleItemBox = function(show){
        if (show){
            iboxopen = true;
            ibox.animate({width:"100%"}, 500);
        } else {
            iboxopen = false;
            ibox.animate({width:"0"}, 500);
        }
    };
    var isDragging = false;
    iboxhandle.on('mousedown', function() {
            $(window).on('mousemove touchmove', function() {
                isDragging = true;
                $(window).unbind("mousemove touchmove");
                $(window).on('mousemove touchmove', function(e) {
                    // get position
                    var parent = $("#iboxhandle").parent().parent();
                    //alert(parent);
                    if (parent.offset()!=undefined){
                        var parentOffset = parent.offset().left + parent.width();
                        var thisOffset = e.pageX;
                        // get width from the right side of the div.
                        var relX = (parentOffset - thisOffset);
                        // work out optimal size
                        if (relX>((parent.width()/2)+2)){
                            ibox.css('width', ibox.css('max-width')); // set max size max size
                        } else {
                            ibox.css('width', relX+"px");
                        }
                        //console.log(parent.offset().left);
                        // set box open indicator
                        iboxopen = (relX>0);
                    } else {
                        ibox.css('width', "0px");//closing too fast hide.
                    }
                });

            });
            $(window).on('mouseup touchcancel', function(){
                stopDragging();
            })
    });
    function stopDragging(){
        var wasDragging = isDragging;
        isDragging = false;
        $(window).unbind("mousemove");
        $(window).unbind("mouseup");
        $(window).unbind("touchmove");
        $(window).unbind("touchcancel");
        if (!wasDragging) { //was clicking
            if (iboxopen){
                toggleItemBox(false);
            } else {
                toggleItemBox(true);
            }
        }
    }
    // close on click outside item box
    $('html').on("click", function() {
        if (iboxopen) toggleItemBox(false); // hide if currently visible
    });
    ibox.on("click", function(event){
        event.stopPropagation();
    });
    // select text of number fields on click
    $(".numpad").on("click", function () {
        $(this).focus().select();
    });
    // keyboard field navigation & shortcuts
    $(document.documentElement).keydown(function (event) {
        // handle cursor keys
        var x;
        var keypad = $(".keypad-popup");
        var paymentsopen = $("#paymentsdiv").is(":visible");
        switch (event.which){
            /*case 37: // left arrow
                keypad.hide();
                x = $('input:not(:disabled), textarea:not(:disabled)');
                x.eq(x.index(document.activeElement) - 1).trigger('click').focus();
                break;
            case 39: // right arrow
                keypad.hide();
                x = $('input:not(:disabled), textarea:not(:disabled)');
                x.eq(x.index(document.activeElement) + 1).trigger('click').focus();
                break;*/
            case 45: // insert
                if ($(":focus").attr('id')=="codeinput"){
                    WPOS.items.addManualItemRow();
                } else {
                    $("#codeinput").trigger('click').focus();
                }
                break;
            case 46: // delete
                WPOS.sales.userAbortSale();
                break;
            case 36: // home

                break;
            case 35: // end
                if (paymentsopen) {
                    WPOS.sales.processSale();
                } else {
                    WPOS.sales.showPaymentDialog();
                }
                break;
        }
        if (paymentsopen) {
            switch (event.which){
                case 90:
                    WPOS.sales.addPayment('cash');
                    break;
                case 88:
                    WPOS.sales.addPayment('credit');
                    break;
                case 67:
                    WPOS.sales.addPayment('eftpos');
                    break;
                case 86:
                    WPOS.sales.addPayment('mpesa');
                    break;
            }
        }
    });

    // dev/demo quick login
    if (document.location.host==="localhost" || document.location.host==="localhost"){
        var login = $("#logindiv");
        login.append('<button class="btn btn-primary btn-sm" onclick="$(\'#username\').val(\'admin\');$(\'#password\').val(\'admin\'); WPOS.userLogin();">Demo Login</button>');
        if (document.location.host==="localhost")
            login.append('<button class="btn btn-primary btn-sm" onclick="$(\'#loginmodal\').hide();">Hide Login</button>');
    }

    // window size
    if (WPOS.getLocalConfig().hasOwnProperty("window_size"))
        $("#wrapper").css("max-width", WPOS.getLocalConfig()["window_size"]);

    // set padding for item list
    setItemListPadding();
    setStatusbarPadding();
    window.onresize = function(){
        setItemListPadding();
        setStatusbarPadding();
    };
});

function setStatusbarPadding(){
    var height = $("#statusbar").height();
    $("#totals").css("margin-bottom", (20+height)+"px");
}

function setItemListPadding(){
    var height = $("#totals").height();
    // $("#items").css("margin-bottom", (80+height)+"px");
}

function expandWindow(){
    var wrapper = $("#wrapper");
    var maxWidth = wrapper.css("max-width");
    switch (maxWidth){
        case "960px":
            wrapper.css("max-width", "1152px");
            WPOS.setLocalConfigValue("window_size", "1152px");
            break;
        case "1152px":
            wrapper.css("max-width", "1280px");
            WPOS.setLocalConfigValue("window_size", "1280px");
            break;
        case "1280px":
            wrapper.css("max-width", "1366px");
            WPOS.setLocalConfigValue("window_size", "1366px");
            break;
        case "1366px":
            wrapper.css("max-width", "none");
            WPOS.setLocalConfigValue("window_size", "none");
            break;
        default:
            wrapper.css("max-width", "960px");
            WPOS.setLocalConfigValue("window_size", "960px");
            break;
    }
}
