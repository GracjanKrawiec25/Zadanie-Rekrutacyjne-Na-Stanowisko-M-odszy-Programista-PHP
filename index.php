<!DOCTYPE html>
<html lang="pl" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Gracjan Krawiec - Aplikacja do pobierania danych z systemu KRS - Zadanie Rekrutacyjne</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="style/style.css">
    <!-- CSS only bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <!-- JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-u1OknCvxWvY5kfmNBILK2hRnQC3Pr17a+RTT6rIHI7NnikvbZlHgTPOOmMi466C8" crossorigin="anonymous"></script>


  </head>
  <body>

    <?php
      if (isset($_POST['submit']) && isset($_POST['krs_number'])) {
          $krs_number = $_POST['krs_number'];
          $krs_select = $_POST['krs_select'];
          if (is_numeric($krs_number)) {



            // Open the file using the HTTP headers set above
              $opts = array(
                'http'=>array(
                  'method'=>"GET",
                  'header'=>"Accept-language: en\r\n" .
                            "Cookie: foo=bar\r\n"
                )
              );

              $querry = 'https://api-krs.ms.gov.pl/api/krs/OdpisPelny/'.$krs_number.'?rejestr='.$krs_select.'&format=json';
              $context = stream_context_create($opts);
              @$file = file_get_contents($querry, true, $context);
              $response_obj = json_decode($file, true);
              // print_r($response_obj);
              if (!empty($response_obj['odpis']['naglowekP']['numerKRS'])) {

                  // stan z dnia
                    $data_from = $response_obj['odpis']['naglowekP']['stanZDnia'];
                  // zmiany w rejestrze
                    $entriesArr = $response_obj['odpis']['naglowekP']['wpis'];
                  // dane firmy
                    $end_company_name = count($response_obj['odpis']['dane']['dzial1']['danePodmiotu']['nazwa']) - 1;
                    $company_name = $response_obj['odpis']['dane']['dzial1']['danePodmiotu']['nazwa'][$end_company_name]['nazwa'];
                    $legal_form = $response_obj['odpis']['dane']['dzial1']['danePodmiotu']['formaPrawna'][0]['formaPrawna'];
                    $krs_no = $response_obj['odpis']['naglowekP']['numerKRS'];
                    $regon_no = $response_obj['odpis']['dane']['dzial1']['danePodmiotu']['identyfikatory'][1]['identyfikatory']['regon'];
                    $nip_no = $response_obj['odpis']['dane']['dzial1']['danePodmiotu']['identyfikatory'][1]['identyfikatory']['nip'];
                    $start_date = $response_obj['odpis']['naglowekP']['wpis'][0]['dataWpisu'];
                    $registry = $response_obj['odpis']['naglowekP']['rejestr'];
                  // dane adresowe
                    $end_address = count($response_obj['odpis']['dane']['dzial1']['siedzibaIAdres']['adres']) - 1;
                    $street = $response_obj['odpis']['dane']['dzial1']['siedzibaIAdres']['adres'][$end_address]['ulica'];
                    $home_number = $response_obj['odpis']['dane']['dzial1']['siedzibaIAdres']['adres'][$end_address]['nrDomu'];
                    $town = $response_obj['odpis']['dane']['dzial1']['siedzibaIAdres']['adres'][$end_address]['miejscowosc'];
                    $postal_code = $response_obj['odpis']['dane']['dzial1']['siedzibaIAdres']['adres'][$end_address]['kodPocztowy'];
                    $country = $response_obj['odpis']['dane']['dzial1']['siedzibaIAdres']['adres'][$end_address]['kraj'];
                  //dane do funduszu
                    if (isset($response_obj['odpis']['dane']['dzial1']['kapital'])) {
                      $end_fund = count($response_obj['odpis']['dane']['dzial1']['kapital']['wysokoscKapitaluZakladowego']) - 1;
                      $fund = $response_obj['odpis']['dane']['dzial1']['kapital']['wysokoscKapitaluZakladowego'][$end_fund]['wartosc'];
                      $curency = $response_obj['odpis']['dane']['dzial1']['kapital']['wysokoscKapitaluZakladowego'][$end_fund]['waluta'];
                    }else{
                      $fund = 'Brak';
                      $curency = 'Danych';
                    }

                  //dane o reprezentacji firmy
                    $authority = $response_obj['odpis']['dane']['dzial2']['reprezentacja'][0]['nazwaOrganu'][0]['nazwaOrganu'];
                    $last_method_rep = count($response_obj['odpis']['dane']['dzial2']['reprezentacja'][0]['sposobReprezentacji']) - 1;
                    $method_representation = $response_obj['odpis']['dane']['dzial2']['reprezentacja'][0]['sposobReprezentacji'][$last_method_rep]['sposobReprezentacji'];
                    $status_opp = (isset($response_obj['odpis']['dane']['dzial1']['danePodmiotu']['czyPosiadaStatusOPP'][0]['czyPosiadaStatusOPP'])) ? $response_obj['odpis']['dane']['dzial1']['danePodmiotu']['czyPosiadaStatusOPP'][0]['czyPosiadaStatusOPP'] : '';
                    $register_authority = $response_obj['odpis']['naglowekP']['wpis'][0]['oznaczenieSaduDokonujacegoWpisu'];
                  // Klasyfikacje firmy
                    $dominant_activity = $response_obj['odpis']['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPrzewazajacejDzialalnosci'];
                    $other_activity = $response_obj['odpis']['dane']['dzial3']['przedmiotDzialalnosci']['przedmiotPozostalejDzialalnosci'];
                ?>

                <div class="popUp active_pop" id="popUp">

                  <div class="row">
                    <div class="col-xs-12 title">
                      <div class="text">Dane Podstawowe - stan na dzień: <?php  echo $data_from;?></div>
                      <div class="close_but" id="close" onclick="closePopup()">
                        Zamknij
                      </div>
                    </div>
                  </div>
                  <div class="row base_data tabs open_tab">

                        <div class="col-xs-12 col-md-6">
                          <div class="box">

                              <span class="title_box">Nazwa firmy:</span>
                                <p><?php echo $company_name;?></p>
                              <span class="title_box">Forma Prawna:</span>
                                <p><?php  echo $legal_form;?></p>
                              <span class="title_box">Numer KRS:</span>
                                <p><?php  echo $krs_no;?></p>
                              <span class="title_box">Numer REGON:</span>
                                <p> <?php  echo $regon_no;?></p>
                              <span class="title_box">Numer NIP:</span>
                                <p><?php  echo $nip_no;?></p>

                          </div>
                        </div>

                        <div class="col-xs-12 col-md-6">
                          <div class="box">

                            <span class="title_box">Rejestr:</span>
                              <p><?php if ($registry == 'RejP'){echo "Przedsiębiorców";}else{echo "Stowarzyszeń";} ?></p>

                            <span class="title_box">Data rozpoczęcia działalności:</span>
                              <p><?php echo $start_date; ?></p>

                            <span class="title_box">Siedziba i adres:</span>
                              <p><?php echo $street." ".$home_number.", ".$town." ".$postal_code."<br/>".$country;?></p>

                            <span class="title_box">Wysokość Kapitału Zakładowego:</span>
                              <p><?php echo $fund." ".$curency; ?></p>

                          </div>
                        </div>



                  </div>


                  <div class="row">
                    <div class="col-xs-12 title">
                      <div class="text">Reprezentacja Firmy</div>
                      <div class="open_but active" id="repre" onclick="tabOpen(this.id)">
                        <img src="src\arrow.png" alt="strzałka do otwarcia">
                      </div>
                    </div>
                  </div>
                  <div class="row repre tabs open_tab">
                    <div class="col-xs-12">
                      <div class="box">
                        <span class="title_box">Nazwa Organu:</span>
                        <p><?php echo $authority; ?></p>
                        <span class="title_box">Sposób Reprezentacj:</span>
                        <p><?php echo $method_representation; ?></p>
                        <span class="title_box">Czy organ posiada status OPP:</span>
                        <p><?php if (empty($status_opp)) {echo " NIE";}else{echo $status_opp;} ?></p>
                        <span class="title_box">Organ Rejestrowy:</span>
                        <p><?php echo $register_authority; ?></p>

                      </div>

                    </div>
                  </div>

                  <div class="row">
                    <div class="col-xs-12 title">
                      <div class="text">Klasyfikacja działalności</div>
                      <div class="open_but active" id="classification" onclick="tabOpen(this.id)">
                        <img src="src\arrow.png" alt="strzałka do otwarcia">
                      </div>
                    </div>
                  </div>
                  <div class="row classification tabs open_tab">
                    <div class="col-xs-12">
                      <div class="box">


                        <span class="title_box">Przedmioty Przeważajacej działalności:</span>

                            <?php
                              $ilosc_pp = count($dominant_activity) - 1;

                                for ($n=0; $n < $ilosc_pp+1 ; $n++) {

                                      foreach ($dominant_activity[$n]['pozycja'] as $classArr) {

                                              echo "<p>".$classArr['opis']." : ".$classArr['kodDzial'].".".$classArr['kodKlasa'].".".$classArr['kodPodklasa']."</p>";
                                      }
                                }
                            ?>

                        <span class="title_box">Przedmioty pozostałej działalności:</span>

                            <?php
                                $ilosc = count($other_activity) - 1;
                                  //print_r($other_activity);
                                  for ($l=0; $l < $ilosc ; $l++) {
                                        $ilosc_p = count($other_activity[$l]['pozycja']) - 1;
                                        foreach ($other_activity[$l]['pozycja'] as $classArr) {
                                          $kodPodklasa = (isset($classArr['kodPodklasa'])) ? $classArr['kodPodklasa'] : '';
                                            $kodKlasa = (isset($classArr['kodKlasa'])) ? $classArr['kodKlasa'] : '';
                                                echo "<p>".$classArr['opis']." : ".$classArr['kodDzial'].".".$kodKlasa.".".$kodPodklasa."</p>";
                                        }
                                  }
                            ?>

                      </div>

                    </div>
                  </div>



                    <div class="row">
                      <div class="col-xs-12 title">
                        <div class="text">Historia zmian w rejestrze</div>
                        <div class="open_but" id="changes_history" onclick="tabOpen(this.id)">
                          <img src="src\arrow.png" alt="strzałka do otwarcia">
                        </div>
                      </div>
                    </div>
                    <div class="row changes_history tabs">
                          <?php
                            foreach ($entriesArr as $entries) {
                              ?>
                                  <div class="col-xs-12 ">
                                    <div class="changes">
                                      <?php
                                        $i = 0;
                                        foreach ($entries as $changes) {
                                          echo '<span class="pole'.$i.'">'.$changes.'</span>';
                                          if($i == 0){echo "";}else{echo "<br/>";}
                                          $i++;
                                        }
                                      ?>
                                    </div>
                                  </div>
                              <?php
                             }
                          ?>
                    </div>

                </div>
                <?php
              }else{
                $errorMsg = "Podany numer KRS nie istnieje w spisie";
              }
              ?>



              <?php
          }else{
            $errorMsg = 'Podana wartość musi być liczbą';
          }
      }else{
      }
    ?>


    <div class="pageContent">
      <div class="slider">
        <div class="slider_img" style="background-image: url('src/slider1.jpg')">

        </div>
        <div class="info">
          <h1>Sprawdź numer KRS</h1>
          <p>Chcesz sprawdzić numer KRS? Z nami zrobisz to szybko i wygodnie w 3 krokach</p>
          <ul>
            <li><b>Krok 1 </b><br/> <p>Wpisz numer KRS</p></li>
            <li><b>Krok 2 </b><br/> <p>Wybierz z listy rejestr</p></li>
            <li><b>Krok 3 </b><br/> <p>Kliknij "Szukaj"</p></li>
          </ul>
        </div>
        <div class="form_box">
          <h2 class="title_form">Dane z Krajowego Rejestru Sądownictwa</h2>
          <form class="krs_form" action="index.php" method="post">
            <label for="krs_number">Wpisz numer KRS:</label>
            <?php if (!empty($errorMsg)) {echo "<p class='error'>".$errorMsg."</p>";}else{}?>
            <input type="text" id="krs_number" name="krs_number" maxlength="10" value="Numer KRS">
            <label for="krs_select">Wybierz rejestr:</label>
            <select class="krs_select" id="krs_select" name="krs_select">
              <option value="P">Przedsiębiorców</option>
              <option value="S">Stowarzyszeń</option>
            </select>
            <input type="submit" class="button" name="submit" value="Szukaj">
          </form>
        </div>
      </div>



    </div>
    <script src="script/main.js" charset="utf-8"></script>
  </body>
</html>
