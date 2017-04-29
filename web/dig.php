<?php
$type = $_GET["type"];
$domain = $_GET["domain_tocheck"];
//$domain = 'persephonefloral.com';


class cnamecheck{
    public $actualdomain = '';
    public $cnamerecord = '';
    public $cnameresult = '';
    public $cnamestatus = '';
    
    function __construct($domain) {
        $this->get_cname($domain);
        $this->actualdomain = $domain;
   }
    
        public function set_status($status) {
        $goodicon = '<div class="successicon showhide"><i class="fa fa-check" aria-hidden="true"></i> OK</div>';
        $badicon = '<div class="failureicon showhide"> &nbsp;<i class="fa fa-times-circle fa-lg" aria-hidden="true"></i>&nbsp; </div>';
        if($status == "good")
        {
            $this->cnamestatus = $goodicon; 
        }
        if($status === "bad")
        {
            $this->cnamestatus = $badicon; 
        }  
    }
    
        function get_cname($domain)
    {
        $result = dns_get_record($domain, DNS_CNAME);
        $this->cnamerecord = $result[0]["target"];
        switch(TRUE)
        {
           case(strpos($this->cnamerecord, 'activehosted.com')): 
                $this->cnameresult = 'Your CNAME appears to be setup correctly! Navigate to your My Settings > Domains page and add it there. It should begin working immediately. Click <a href="https://help.activecampaign.com/hc/en-us/articles/207331210-How-do-I-use-a-custom-domain-name-CNAME-">here</a> for more info.';
                $this->set_status('good');
                break;
            case(!empty($this->cnamerecord) && !strpos($this->cnamerecord, 'activehosted.com')): 
                $this->cnameresult = "Your appear to have a CNAME setup, but it's not the correct CNAME. Your current CNAME record is:<br><code>" . $this->cnamerecord . '</code><br> It should be:<br><code>YOURACCOUNT.activehosted.com</code>';
                $this->set_status('bad');
                break;
            case(strpos($this->cnamerecord, '"')): 
                $this->cnameresult = "Your CNAME record has quotes. Please remove the quotes. Your current CNAME record is:<br><code>" . $this->cnamerecord . '</code>';
                $this->set_status('bad');
                break;
            case(empty($this->cnamerecord)):
                $this->cnameresult = "There is no CNAME record at your domain. It's possible you didn't set it up correctly or that your DNS just needs more time to propagate.";
                $this->set_status('bad');
                $this->cnamerecord = ' ';
                break;
    }
}
}

class dnscheck {
    public $actualdomain = '';
    public $dkimrecord = '';
    public $dkimresult = '';
    public $dkimstatus = '<div class="failureicon showhide"> &nbsp;<i class="fa fa-times-circle fa-lg" aria-hidden="true"></i>&nbsp; </div>';
    public $dmarcrecord = '';
    public $dmarcresult = '';
    public $dmarcstatus = '<div class="neutralicon showhide"><i class="fa fa-check" aria-hidden="true"></i> OK</div>';
    public $spfrecord = '';
    public $spfresult = "There is no SPF record at your domain.";
    public $spfstatus = '<div class="failureicon showhide"> &nbsp;<i class="fa fa-times-circle fa-lg" aria-hidden="true"></i>&nbsp; </div>';
    public $correctdkim = 'v=DKIM1;p=MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDhkOo8s6fh9Byz1uy69tfQ6eUnzi/5P22EWccwI1PdmCpiyNwZcq3vOS2MHbVYB+ZY6wbBlAFym8EHbZY9OTlJ3+dzt8qTUNW5olkNVl4ecDv3XO2ML8q5sxQL+dwQU6UAQiDAAC/ZRWwiXHrSsr90pqH1Q0vhB7Kp6DHrWYJquQIDAQAB';  
    
    function __construct($domain) {
        $this->get_spf($domain);
        $this->get_dkim($domain);
        $this->get_dmarc($domain);
        $this->actualdomain = $domain;
   }
    
    public function set_status($status, $record) {
        $goodicon = '<div class="successicon showhide"><i class="fa fa-check" aria-hidden="true"></i> OK</div>';
        $badicon = '<div class="failureicon showhide"> &nbsp;<i class="fa fa-times-circle fa-lg" aria-hidden="true"></i>&nbsp; </div>';
        $neutralicon= '<div class="neutralicon showhide"><i class="fa fa-check" aria-hidden="true"></i> OK</div>';
        if($status == "good" and $record == "spf")
        {
            $this->spfstatus = $goodicon; 
        }
        if($status === "bad" and $record === "spf")
        {
            $this->spfstatus = $badicon; 
        } 
        if($status === "good" and $record === "dkim")
        {
            $this->dkimstatus = $goodicon; 
        }     
        if($status === "bad" and $record === "dkim")
        {
            $this->dkimstatus = $badicon; 
        }  
        if($status === "bad" and $record === "dmarc")
        {
            $this->dmarcstatus = $badicon; 
        }
        if($status === "good" and $record === "dmarc")
        {
            $this->dmarcstatus = $goodicon; 
        }
        if($status === "neutral" and $record === "dmarc")
        {
            $this->dmarcstatus = $neutralicon; 
        }
    }
    
    function get_dmarc($domain)
    {
        $result = dns_get_record("_dmarc." . $domain, DNS_TXT);
        $this->dmarcrecord = $result[0]["txt"];
        switch(TRUE)
        {
           case(strpos($this->dmarcrecord, '"')): 
                $this->dmarcresult = "DMARC record has quotes. Please remove the quotes.<br><br>Your actual DMARC record is:<br><code>" . $this->dmarcrecord . '</code>';
                $this->set_status('bad', 'dmarc');
                break;
            case(strpos($this->dmarcrecord, 'reject') || strpos($this->dmarcrecord, 'quarantine')): 
                $this->dmarcresult = "You have a p=reject or p=quarantine DMARC record. This means all your mail from ActiveCampaign will most likely bounce unless you have successfully setup DKIM. Either setup DKIM for this domain, or change the policy in your DMARC record to p=none<br><br>Your actual DMARC record is:<br><code>" . $this->dmarcrecord . '</code>';
                $this->set_status('bad', 'dmarc');
                break;
            case(strpos($this->dmarcrecord, 'none') && strpos($this->dmarcrecord, 'DMARC1')): 
                $this->dmarcresult = "Your DMARC record looks great!<br><br>Your actual DMARC record is:<br><code>" . $this->dmarcrecord . '</code>';
                $this->set_status('good', 'dmarc');
                break;
            case(empty($this->dmarcrecord)):
                $this->dmarcresult = "There is no DMARC key at your domain. That is just fine. DMARC is not required for delivery, and setting it up has no major impact. If you think someone is trying to phish with your domain, then you should consider setting up DMARC.";
                $this->set_status('neutral', 'dmarc');
                $this->dmarcrecord = ' ';
                break;
        }  
    }
    function get_dkim($domain)
    {
        $result = dns_get_record("dk._domainkey." . $domain, DNS_TXT);
        $this->dkimrecord = $result[0]["txt"];
        
        switch(TRUE)
        {
           case(false !== strpos($this->dkimrecord, '"')): 
                $this->dkimresult = 'Your DKIM Key has quotes. Please remove the quotes.<br><br> This is your current DKIM record:<br><code class="wrap">' . $this->dkimrecord . '</code>';
                $this->set_status('bad', 'dkim');
                break;
            case($this->dkimrecord == $this->correctdkim):
                $this->dkimresult = "Your DKIM key is correct!";
                $this->set_status('good', 'dkim');
                $this->dkimrecord = ' ';
                break;  
            case(strpos($this->dkimrecord, 'DKIM1')):
                $this->dkimresult = 'You seem to have a DKIM key, but not the right one. Please navigate to the My Settings > Advanced page in your ActiveCampaign account and generate the DKIM key again (it won\'t change). Make sure you\'ve copied the correct key into your DNS records.<br><br> This is your current DKIM record:<br><code class="wrap">' . $this->dkimrecord . '</code>';
                $this->set_status('bad', 'dkim');
                break;
            case(empty($this->dkimrecord)):
                $this->dkimresult = 'There is no DKIM key at your domain. If you have enabled the option "I will manage my own email authentication" this will cause deliverability problems. Please fix your DKIM key or disable this option.';
                $this->set_status('bad', 'dkim');
                $this->dkimrecord = ' ';
                break;
        }      
    }
    function get_spf($domain)
    {
        $result = dns_get_record($domain, DNS_TXT);
        $spfcounter = 0;
        foreach($result as $record)
        {
            switch(TRUE)
            {
                case(!strpos($record["txt"], 'spf1')): 
                    break;
                case(strpos($record["txt"], 'spf1 ') && $spfcounter > 0): 
                    $this->spfrecord .= '<br>' . $record["txt"];
                    $this->set_status('bad', 'spf');
                    $this->spfresult = 'You have multiple SPF records. SPF policy only permits you to have one. You should just add include:emsd1.com to your current SPF record instead of adding a new one entirely. Your correct SPF record should look something like this:<br> <code>v=spf1 include:google.com include:emsd1.com ... ~all</code><br><br> Here are the actual SPF records you have setup:<br><code>' . $this->spfrecord . '</code>';
                    break;
                case(strpos($record["txt"], 'spf1 ') &&  strpos($record["txt"], 'all') && strpos($record["txt"], 'include:emsd1.com')):
                    $this->spfrecord = $record["txt"] . '<br>';
                    $this->set_status('good', 'spf');
                    $spfcounter++;
                    $this->spfresult = "Your SPF record is correct! <br><br> Your SPF record is:<br><code>" . $this->spfrecord . "</code>";
                    break;  
                case(strpos($record["txt"], 'spf1 ') &&  strpos($record["txt"], 'all') && !strpos($record["txt"], 'include:emsd1.com')):
                    $this->spfrecord = $record["txt"];
                    $this->set_status('bad', 'spf');
                    $spfcounter++;
                    $this->spfresult = 'You have an SPF record but it does not whitelist our servers inclue:emsd1.com <br><br>This is your current SPF record:<br><code>' . $this->spfrecord . '</code>';
                    break;  
            }  
        }
    }
}
if($type == "normal")
{
    $testresults = new dnscheck($domain);
    echo json_encode($testresults, JSON_PRETTY_PRINT);
}
if($type == "cname")
{
    $testresults = new cnamecheck($domain);
    echo json_encode($testresults, JSON_PRETTY_PRINT);
}
