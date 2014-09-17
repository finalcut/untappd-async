Untappd-async
=======================
#### A high performance PHP library for the Untappd version 4 API

----------------------------------------

### An example usage

Included in the repository are unit tests inside the `tests` directory as well as an easy to run script names `simpleTest.php`. You can load `simpleTest.php` up in a browser and you should see it working.

    $Untappd = new EpiUntappd($clientId, $clientSecret);
    // http://api.untappd.com/v4/checkin/add
    $checkin = $Untappd->post('/checkin/add', array(
        'gmt_offset' => '-5', 'timezone' => 'EST', 'bid'=>3358
      )
    );
    echo "This is your {$checkin->response->stats->beer_month} beer this month and your {$checkin->response->stats->beer_month} overall.";


### Documentation

There's complete documentation available on Github at <https://github.com/finalcut/untappd-async/wiki>

### The authors

Get in touch with the author if you have suggestions or questions.
<table>
  <tr>
    <td><img src="http://www.gravatar.com/avatar/34f0e28f985a8e2ce7638f48708981c0?s=60"></td><td valign="middle">Bill Rawlinson<br>bill-at-rawlinson.us<br><a href="http://rawlinson.us">http://rawlinson.us</a></td>
  </tr>
</table>

Note: this is based entirely on the work done on the foursquare-async library <https://github.com/jmathai/Untappd-async>
