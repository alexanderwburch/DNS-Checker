<html>

<head>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.2.0.min.js" integrity="sha256-JAW99MJVpJBGcbzEuXk4Az05s/XyDdBomFqNlM3ic+I=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
    <script src="https://use.fontawesome.com/aac1786664.js"></script>

    <script src="https://code.jboxcdn.com/0.4.7/jBox.min.js"></script>
    <link href="https://code.jboxcdn.com/0.4.7/jBox.css" rel="stylesheet">
    <link rel="shortcut icon" href="./favicon.ico" type="image/x-icon" />
    <title>Domain Authentication Checker</title>
    <link rel="stylesheet" href="./skin.css">

    <script>
        window.onload = function() {
            populateTables()
        };

        function getParameterByName(name, url) {
            if (!url) {
                url = window.location.href;
            }
            name = name.replace(/[\[\]]/g, "\\$&");
            var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
                results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, " "));
        }

        function checkdns(typetocheck, domain, callback) {
            domain = domain.replace(/^https?\:\/\//i, "");
            domain = domain.replace(/\/$/, "");
            if (document.cookie.indexOf(domain) < 0) {
                // set a new cookie
                expiry = new Date();
                expiry.setTime(expiry.getTime() + (90 * 24 * 60 * 60 * 1000)); // 90 days
                // Date()'s toGMTSting() method will format the date correctly for a cookie
                if (typetocheck == "cname") {
                    document.cookie = "cname_" + domain + "=" + domain + "; expires=" + expiry.toGMTString();
                }
                if (typetocheck == "normal") {
                    document.cookie = "hist_" + domain + "=" + domain + "; expires=" + expiry.toGMTString();
                }
            }

            $.ajax({
                type: "GET",
                url: "dig.php",
                data: {
                    domain_tocheck: domain,
                    type: typetocheck
                },
                success: callback,
            });
        }

        function updateheroresult_andaddtotable(data) {
            var decodeddata = JSON.parse(data);
            buildrow(decodeddata.actualdomain, decodeddata.dkimresult, decodeddata.dkimrecord, decodeddata.spfresult, decodeddata.spfrecord, 1, decodeddata.dkimstatus, decodeddata.spfstatus, decodeddata.dmarcresult, decodeddata.dmarcstatus, decodeddata.dmarcrecord);
            //write function that updates the TDs in the first row
        }

        function updateheroresult_andaddtocnametable(data) {
            var decodeddata = JSON.parse(data);
            buildcnamerow(decodeddata.actualdomain, decodeddata.cnameresult, decodeddata.cnamerecord, decodeddata.cnamestatus, 1);
            //write function that updates the TDs in the first row
        }

        function buildrow(domain, dkimresult, dkimrecord, spfresult, spfrecord, highlight, dkimstatus, spfstatus, dmarcresult, dmarcstatus, dmarcrecord) {
            var table = document.getElementById("history_table");

            var hiddenrow = table.insertRow(0);
            var statusrow = table.insertRow(0);
            statusrow.id = domain;
            $(hiddenrow).addClass("hiddenrow");

            var expandrow_cell = statusrow.insertCell(0);
            var domain_cell = statusrow.insertCell(1);
            var dkimstatus_cell = statusrow.insertCell(2);
            var spfstatus_cell = statusrow.insertCell(3);
            var dmarcstatus_cell = statusrow.insertCell(4);
            var recheck_cell = statusrow.insertCell(5);
            var removerow_cell = statusrow.insertCell(6);

            var blank_cell = hiddenrow.insertCell(0);
            var blank_cell2 = hiddenrow.insertCell(1);
            var dkim_result_cell = hiddenrow.insertCell(2);
            var spf_result_cell = hiddenrow.insertCell(3);
            var dmarc_result_cell = hiddenrow.insertCell(4);

            expandrow_cell.innerHTML = '<i class="fa fa-plus-square-o fa-lg showhide" aria-hidden="true"></i>';
            domain_cell.innerHTML = domain;
            spfstatus_cell.innerHTML = spfstatus;
            dkimstatus_cell.innerHTML = dkimstatus;
            dmarcstatus_cell.innerHTML = dmarcstatus;
            recheck_cell.innerHTML = '<button class="recheckbutton recheck">Retest</button>';
            removerow_cell.innerHTML = '<i class="btndelete fa-lg fa fa-times" aria-hidden="true"></i>';
            dkim_result_cell.innerHTML = '<span id="dkimresult">' + dkimresult + '</span><br>';
            spf_result_cell.innerHTML = '<span id="spfresult">' + spfresult + '</span><br><span id="spfrecord"></span>';
            dmarc_result_cell.innerHTML = '<span id="dmarcresult">' + dmarcresult + '</span><br><span id="dmarcrecord"></span>';

            if (highlight !== 0) {
                var $newRow = $(statusrow);
                $newRow.effect("highlight", {
                    color: "#e2e0e0"
                }, 2000);

            }

        }

        function buildcnamerow(domain, cnameresult, cnamerecord, cnamestatus, highlight) {
            var table = document.getElementById("cname_history_table");

            var hiddenrow = table.insertRow(0);
            var statusrow = table.insertRow(0);
            statusrow.id = domain;
            $(hiddenrow).addClass("hiddenrow");

            var expandrow_cell = statusrow.insertCell(0);
            var domain_cell = statusrow.insertCell(1);
            var cnamestatus_cell = statusrow.insertCell(2);
            var recheck_cell = statusrow.insertCell(3);
            var removerow_cell = statusrow.insertCell(4);
            var blank_cell = hiddenrow.insertCell(0);
            var blank_cell2 = hiddenrow.insertCell(1);
            var cname_result_cell = hiddenrow.insertCell(2);


            expandrow_cell.innerHTML = '<i class="fa fa-plus-square-o fa-lg showhide" aria-hidden="true"></i>';
            domain_cell.innerHTML = domain;
            cnamestatus_cell.innerHTML = cnamestatus;
            recheck_cell.innerHTML = '<button class="recheckbutton recheck">Retest</button>';
            removerow_cell.innerHTML = '<i class="btndelete fa-lg fa fa-times" aria-hidden="true"></i>';
            cname_result_cell.innerHTML = '<span id="cnameresult">' + cnameresult + '</span>';

            if (highlight !== 0) {
                var $newRow = $(statusrow);
                //$('#dns_table tr:last').after($newRow);
                $newRow.effect("highlight", {
                    color: "#e2e0e0"
                }, 2000);
            }

        }

        function populateTables() {
            var existingrows = [];
            var existingcnamerows = [];
            var ca = document.cookie.split(';');
            for (var i = 0; i < 10; i++) {
                debugger;
                var c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1);
                if (c.indexOf("cname_") === 0) {
                    var parts = c.split("=");
                    var domain = parts[1];

                    if (-1 == $.inArray(domain, existingrows)) {
                        checkdns("cname", domain, function(data) {
                            var decodeddata = JSON.parse(data);
                            //console.log(decodeddata);
                            buildcnamerow(decodeddata.actualdomain, decodeddata.cnameresult, decodeddata.cnamerecord, decodeddata.cnamestatus, 0);
                        });
                    }
                    existingcnamerows.push(domain);
                };
                if (c.indexOf("hist_") === 0) {
                    var parts = c.split("=");
                    var domain = parts[1];

                    if (-1 == $.inArray(domain, existingrows)) {
                        checkdns("normal", domain, function(data) {
                            var decodeddata = JSON.parse(data);

                            buildrow(decodeddata.actualdomain, decodeddata.dkimresult, decodeddata.dkimrecord, decodeddata.spfresult, decodeddata.spfrecord, 0, decodeddata.dkimstatus, decodeddata.spfstatus, decodeddata.dmarcresult, decodeddata.dmarcstatus, decodeddata.dmarcrecord);
                        });
                    }
                    existingrows.push(domain);

                };
                var domains = getParameterByName('domains');
                if (domains != null && domains != '' && domains != undefined) {

                    var domainarr = domains.split("1a1");
                    domainarr.forEach(function(item) {
                        if (-1 == $.inArray(item, existingrows)) {
                            checkdns("normal", item, function(data) {
                                var decodeddata = JSON.parse(data);

                                buildrow(decodeddata.actualdomain, decodeddata.dkimresult, decodeddata.dkimrecord, decodeddata.spfresult, decodeddata.spfrecord, 0, decodeddata.dkimstatus, decodeddata.spfstatus, decodeddata.dmarcresult, decodeddata.dmarcstatus, decodeddata.dmarcrecord);
                            });
                            existingrows.push(item);
                        }
                    });
                }


                var cnames = getParameterByName('cnames');
                //console.log(cnames);

                if (cnames != null && cnames != "" && cnames != undefined) {
                    var cnamesarr = cnames.split("1a1");
                    cnamesarr.forEach(function(item) {
                        if (-1 == $.inArray(item, existingcnamerows)) {
                            checkdns("cname", item, function(data) {
                                var decodeddata = JSON.parse(data);
                                //console.log(decodeddata);
                                buildcnamerow(decodeddata.actualdomain, decodeddata.cnameresult, decodeddata.cnamerecord, decodeddata.cnamestatus, 0);
                            });
                            existingcnamerows.push(item);
                        }
                    });
                }
            }
        }

    </script>


    <style>


    </style>
    <base target="_blank">
</head>

<body>
    <header id="HEADER_1">
        <div id="DIV_22">
            <div id="DIV_33">
                <div id="DIV_44">
                    <div id="DIV_55">
                        <a href="http://www.activecampaign.com" id="A_6"><img src="https://www.activecampaign.com/images/head_logo.png" id="IMG_7" alt='' /></a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div class="maincontainer">
        <div class="inputarea">
            <h3>SPF/DKIM/DMARC</h3>
            <p>This tool will allow you to check if your domain has appropriate DNS records for email authentication. </p>
            <p> Each domain host has a particular way of adding/editing DNS records. If you don't know your domain host (e.g. Godaddy), look it up <a href="https://www.webhostinghero.com/who-is-hosting/">here</a>. After adding the DNS records, enter your domain below to check if everything is working. Note that records can sometimes take a few hours to propagate. </p>
            <p>Please be aware that setting up DKIM/SPF/DMARC is not a "boost" for deliverability, and generally you won't see any noticeable improvement to deliverability after setting up authentication. For more information on why you may want to setup authentication for your domain, check out our <a href='https://help.activecampaign.com/hc/en-us/articles/206903370-DKIM-SPF-and-DMARC'>Domain Authentication help doc</a>.</p>
            <p>*You can setup domain authentication for as many domains as necessary. If you have enabled "I will manage my own email authentication", you need to setup authentication for all the From Address domains you use.</p>
            <input id="normaldomain" class="inputbox" type="text" name="domain">

            <button id="addrow" class="button" type="button" onclick='checkdns("normal", document.getElementById("normaldomain").value, updateheroresult_andaddtotable)'>Enter</button>

        </div>


        <div id="DIV_1">
            <div id="DIV_2">
                SPF/DKIM/DMARC Results <span class="sharebutton"><i class="fa fa-link" aria-hidden="true"></i></span>
            </div>
            <div id="DIV_3">
                <div id="DIV_5">
                    <table class="TABLE_6" id="dns_table">
                        <thead class="header_row">
                            <tr id="TR_8">
                                <th class="expand_column">

                                </th>
                                <th class="domain_column">
                                    Domain
                                </th>
                                <th class="result_column">
                                    DKIM
                                </th>
                                <th class="result_column">
                                    SPF
                                </th>
                                <th class="result_column">
                                    DMARC
                                </th>
                                <th class="recheck_column">

                                </th>
                                <th class="deleterow_column">

                                </th>
                            </tr>
                        </thead>
                        <tbody id="history_table">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="inputarea">
            <h3>Domain Alias (CNAME)</h3>
            <p>Enter your CNAME domain below. This can be any subdomain of your domain. You should place a CNAME record at this subomdomain that points to YOURACCOUNT.activehosted.com. Typically something like email.mydomain.com. Once this is setup, the subdomain can't be used for anything else.</p>
            <p>CNAMES do not have a large impact on deliverability. The most common reason to setup a CNAME is for whitelabeling purposes, because this will change the login and link tracking domain (e.g. activehosted.com) to your own domain.</p>
            <p><strong>Need help?</strong> Check out our <a href='https://help.activecampaign.com/hc/en-us/articles/207331210-How-do-I-use-a-custom-domain-name-CNAME-'>CNAME help doc</a></p>
            <input id="cnamedomain" class="inputbox" type="text" name="domain">
            <button id="addrow" class="button" type="button" onclick='checkdns("cname", document.getElementById("cnamedomain").value, updateheroresult_andaddtocnametable)'>Enter</button>
        </div>

        <div id="DIV_1">
            <div id="DIV_2">
                CNAME Results <span id="Tooltip-3" class="sharebutton"><i class="fa fa-link" aria-hidden="true"></i></span>
            </div>
            <div id="DIV_3">
                <div id="DIV_5">
                    <table class="TABLE_6" id="cname_table">
                        <thead class="header_row">
                            <tr id="TR_8">
                                <th class="expand_column">

                                </th>
                                <th class="domain_column">
                                    Domain
                                </th>
                                <th class="result_column">
                                    CNAME
                                </th>
                                <th class="recheck_column">

                                </th>
                                <th class="deleterow_column">

                                </th>
                            </tr>
                        </thead>
                        <tbody id="cname_history_table">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>



    <script>
        $(document).ready(function() {


            new jBox('Tooltip', {
                theme: 'TooltipDark',
                attach: $('.sharebutton'),
                closeOnMouseleave: true,
                onOpen: function() {
                    var sharelink = 'https://dkimspfchecker.herokuapp.com/';
                    sharelink += '?cnames=';
                    $("#cname_history_table > tr:even").find("td:eq(1)").each(function(index) {
                        //console.log(index + ": " + $(this).text());
                        sharelink += $(this).text() + "1a1";
                    });

                    sharelink += '&domains=';
                    sharelink = sharelink.replace("1a1&", "&");
                    $("#history_table > tr:even").find("td:eq(1)").each(function(index) {
                        //console.log(index + ": " + $(this).text());
                        sharelink += $(this).text() + "1a1";
                    });
                    sharelink += '$';
                    sharelink = sharelink.replace("1a1$", "");
                    sharelink = sharelink.replace("=$", "=");
                    $(".popupbox").text(sharelink);
                },
                content: '<span class="popupbox"></span>'
            });


            $(".hiddenrow").hide();
            $("tbody > tr:first").show();
            $("#history_table").on('click', '.showhide', function() {
                $(this).closest('tr').next('tr').toggle();
            });

            $("#history_table").on('click', '.btndelete', function() {
                $(this).closest('tr').next('tr').remove();
                $(this).closest('tr').remove();
                var currentRow = $(this).closest('tr');
                var currentrowvalue = currentRow.find("td:eq(1)").text();
                document.cookie = 'hist_' + currentrowvalue + '=' + currentrowvalue + '; expires=Thu, 01-Jan-70 00:00:01 GMT;';
            });

            $("#history_table").on('click', '.recheck', function() {
                var domain = $(this).closest('tr').find("td:eq(1)").text();
                $(this).closest('tr').next('tr').remove();
                $(this).closest('tr').remove();
                checkdns("normal", domain, function(data) {

                    var decodeddata = JSON.parse(data);
                    //console.log(decodeddata);
                    buildrow(decodeddata.actualdomain, decodeddata.dkimresult, decodeddata.dkimrecord, decodeddata.spfresult, decodeddata.spfrecord, 1, decodeddata.dkimstatus, decodeddata.spfstatus, decodeddata.dmarcresult, decodeddata.dmarcstatus, decodeddata.dmarcrecord);
                });
            });


            $("#cname_history_table").on('click', '.showhide', function() {
                $(this).closest('tr').next('tr').toggle();
            });

            $("#cname_history_table").on('click', '.btndelete', function() {
                $(this).closest('tr').next('tr').remove();
                $(this).closest('tr').remove();
                var currentRow = $(this).closest('tr');
                var currentrowvalue = currentRow.find("td:eq(1)").text();
                document.cookie = 'cname_' + currentrowvalue + '=' + currentrowvalue + '; expires=Thu, 01-Jan-70 00:00:01 GMT;';
            });

            $("#cname_history_table").on('click', '.recheck', function() {
                var domain = $(this).closest('tr').find("td:eq(1)").text();
                $(this).closest('tr').next('tr').remove();
                $(this).closest('tr').remove();
                checkdns("cname", domain, function(data) {

                    var decodeddata = JSON.parse(data);
                    //console.log(decodeddata);
                    buildcnamerow(decodeddata.actualdomain, decodeddata.cnameresult, decodeddata.cnamerecord, decodeddata.cnamestatus, 1);
                });
            });
        });

    </script>

</body>

</html>
