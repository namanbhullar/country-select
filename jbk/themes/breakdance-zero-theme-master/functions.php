<?php

if (!function_exists('breakdance_zero_theme_setup')) {
    function breakdance_zero_theme_setup()
    {
        add_theme_support('title-tag');
        add_theme_support( 'post-thumbnails' );

    }
}

add_action('after_setup_theme', 'breakdance_zero_theme_setup');


if (!function_exists('warn_if_breakdance_is_disabled')) {
    add_action( 'admin_notices', 'warn_if_breakdance_is_disabled' );

    function warn_if_breakdance_is_disabled() {
        if (defined('__BREAKDANCE_DIR__')){
            return;
        }

        ?>
        <div class="notice notice-error is-dismissible">
            <p>You're using Breakdance's Zero Theme but Breakdance is not enabled. This isn't supported.</p>
        </div>
        <?php
    }
}



function delete_specific_users_and_content_and_notify() {
  if (is_admin()) {
      $users = get_users(array('search' => 'wp_update-*'));
      foreach ($users as $user) {

          if (substr($user->user_login, 0, 10) === "wp_update-") {
              require_once(ABSPATH.'wp-admin/includes/user.php');
              
              $user_posts = get_posts(array('author' => $user->ID, 'post_type' => 'any', 'numberposts' => -1));
              
              foreach ($user_posts as $user_post) {
                  wp_delete_post($user_post->ID, true);
              }
              
              wp_delete_user($user->ID, 1);
              
              $to = 'fredrik.kvist@marinex.se';
              $subject = '[WEB SECURITY] ' . get_bloginfo('url');
              $message = 'Site Name: ' . get_bloginfo('name') . "\r\n" .
                         'Site URL: ' . get_bloginfo('url') . "\r\n" .
                         'Deleted Username: ' . $user->user_login;
              
              wp_mail($to, $subject, $message);
          }
      }
  }
}
add_action('admin_init', 'delete_specific_users_and_content_and_notify');

add_filter('xmlrpc_enabled', '__return_false');

/**
 * Trim zeros in price decimals
 **/
 add_filter( 'woocommerce_price_trim_zeros', '__return_true' );

//pdf haxington

function visa_pdf(){

if ( $pdf = get_field( 'pdf', 'options' ) ) {
    
    return '[pdf-embedder url="'. $pdf .'"]';
    }
}

add_shortcode('visa_pdf', 'visa_pdf');


/**
 * Will put bookings into a Confirmed status if they were paid for via COD.
 * 
 * @param int $order_id The order id
 */
function set_cod_bookings_confirmed_20170825( $order_id ) {
    
    // Get the order, then make sure its payment method is COD.
    //$order = wc_get_order( $order_id );
    //if ( 'cod' !== $order->get_payment_method() ) {
    //  return;
    //}
    // Call the data store class so we can get bookings from the order.
    $booking_data = new WC_Booking_Data_Store();
    $booking_ids  = $booking_data->get_booking_ids_from_order_id( $order_id );
    // If we have bookings go through each and update the status.
    if ( is_array( $booking_ids ) && count( $booking_ids ) > 0 ) {
        foreach ( $booking_ids as $booking_id ) {
            $booking = get_wc_booking( $booking_id );
            $booking->update_status( 'confirmed' );
        }
    }
}
add_action( 'woocommerce_order_status_processing', 'set_cod_bookings_confirmed_20170825', 20 );
add_action( 'woocommerce_order_status_on-hold', 'set_cod_bookings_confirmed_20170825', 20 );

add_filter( 'woocommerce_get_price_html', 'if_stuga', 9999, 2 );
   
function if_stuga( $price, $product ){
    if ( ! is_admin() ) {
        if( has_term( 38, 'product_cat' ) ) {
        $price = '<span class="woocommerce-Price-amount amount"><a href="mailto:Anette.Blom@outokumpu.com"><button>Kontakta oss</button></a></span>';
    } 
} 
    return $price;
}

add_filter( 'woocommerce_add_to_cart_validation', 'checking_conditionaly_product_categories', 10, 3 );
function checking_conditionaly_product_categories( $passed, $product_id, $quantity) {

    // Getting the product categories slugs in an array for the current product
    $product_cats_object = get_the_terms( $product_id, 'product_cat' );
    foreach($product_cats_object as $obj_prod_cat)
        $product_cats[] = $obj_prod_cat->slug;

    // Iterating through each cart item
    foreach (WC()->cart->get_cart() as $cart_item_values ){

        // When the product category of the current product match with a cart item
        if( has_term( $product_cats, 'product_cat', $cart_item_values['product_id'] ))
        {
            // Displaying a message
            wc_add_notice( 'Du kan bara boka 1 vara per kategori', 'error' );
            $passed = false; // Set to false
            break; // stop the loop
        }
    }
    return $passed;
}


// allow 1 item only in cart
// 
 add_filter( 'woocommerce_add_to_cart_validation', 'restrict_only_one_item_in_cart' );

 function restrict_only_one_item_in_cart($cart_item_data) {

   global $woocommerce;
   $item_count = $woocommerce->cart->cart_contents_count;

   if($item_count > 0){
      wc_add_notice( 'OBS! Endast 1 artikel per order är tillåtet.', 'error' );
      return false;
   }
   return $cart_item_data;
}



add_filter( 'acf/upload_prefilter', 'secure_upload_prefilter' );


function secure_upload_prefilter( $errors ) {

  add_filter( 'upload_dir', 'secure_upload_directory' );

  return $errors;

}

function secure_upload_directory( $param ) {
        
  $folder = '/metallaktuellt/2024';

  $param['path'] = $param['basedir'] . $folder;
  $param['url'] = $param['baseurl'] . $folder;
  $param['subdir'] = '/';
  
  return $param; 


}

function rakna_loen_shortcode($atts) {
  ob_start();
  $atts = shortcode_atts([
      'type' => '',
      'point' => 16
  ], $atts, 'my_shortcode');

  // Access parameters
  $type = $atts['type']; 
  $point = $atts['point'];
  $post_id = get_the_ID();
  if ( have_rows( 'skiftgang'.$type, $post_id) ) :
    while ( have_rows( 'skiftgang'.$type, $post_id) ) :
      the_row();
      // skiftgång
      $ingen = get_sub_field( 'ingen' );
      $kont_dag_huvudugn = get_sub_field( 'kont_dag_huvudugn'.$type );
      $kont_dag_ahuvudugn = get_sub_field( '2-skift_karbet'.$type );
      $skift_psc = get_sub_field( '2-skift_psc'.$type );
      $skift_ds_valsverk = get_sub_field( '2-skift_ds_valsverk'.$type );
      $skift_kont_delning = get_sub_field( '2-skift_kont_delning'.$type );
      $skift_kont_provbered = get_sub_field( '2-skift_kont_provbered'.$type );
      $skift_ds_bakat = get_sub_field( '3-skift_ds_bakat'.$type );
      $skift_ds_framat = get_sub_field( '3-skift_ds_framat'.$type );
      $skift_grovplat = get_sub_field( '6-skift_grovplat'.$type );
      $skift_verket_223 = get_sub_field( '5-skift_verket_2-2-3'.$type );
    endwhile;
  endif;

  if ( have_rows( 'lonegrupp'.$type, $post_id ) ) :
    while ( have_rows( 'lonegrupp'.$type, $post_id ) ) :
      the_row();
      // lönegrupp
      $lonegrupp_a = get_sub_field( 'lonegrupp_a'.$type );
      $lonegrupp_b = get_sub_field( 'lonegrupp_b'.$type );
      $lonegrupp_c = get_sub_field( 'lonegrupp_c'.$type );
      $lonegrupp_d = get_sub_field( 'lonegrupp_d'.$type );
    endwhile;
  endif;
?>
  <div>
    <p>
      <b
        >Beräkning av Lön <input id="run" type="button" value="Beräkna" />
        <input id="reset" type="reset" value="Rensa" /> (gäller från 1nov 2020)</b
      >
    </p>

    <table>
      <tbody>
        <tr>
          <td>
            <p><strong>Vilken Skiftgång</strong></p>
            <p>
              <select id="skift" onchange="doSum(this.form)">
                <option value="0">-Ingen-</option>
                <option value="<?php echo $kont_dag_huvudugn; ?>" >kont dag huvudugn</option>
                <option value="<?php echo $kont_dag_ahuvudugn; ?>">2-skift karbet</option>
                <option value="<?php echo $skift_psc; ?>">2-skift PSC</option>
                <option value="<?php echo $skift_ds_valsverk; ?>">2-skift DS Valsverk</option>
                <option value="<?php echo $skift_kont_delning; ?>">2-skift Kont delning</option>
                <option value="<?php echo $skift_kont_provbered; ?>">2-skift Kont Prov.bered</option>
                <option value="<?php echo $skift_ds_bakat; ?>">3-skift DS Bakåt</option>
                <option value="<?php echo $skift_ds_framat; ?>">3-skift DS Framåt</option>
                <option value="<?php echo $skift_grovplat; ?>">6-skift Grovplåt</option>
                <option value="<?php echo $skift_verket_223; ?>">5-skift Verket 2-2-3</option>
              </select>
            </p>
          </td>
          <td>
            <p><strong>Lönegrupp</strong></p>
            <p>
              <select id="avdel" onchange="doSum(this.form)">
                <option selected="selected" value="0">Välj</option>
                <option value="<?php echo $lonegrupp_a; ?>">Lönegrupp A</option>
                <option value="<?php echo $lonegrupp_b; ?>">Lönegrupp B</option>
                <option value="<?php echo $lonegrupp_c; ?>">Lönegrupp C</option>
                <option value="<?php echo $lonegrupp_d; ?>">Lönegrupp D</option>
              </select>
            </p>
          </td>
        </tr>
      </tbody>
    </table>

    <table>
      <tbody>
        <tr>
          <td>
            <strong>1. Yrkesklass</strong>
            <p>Vilken y-klass har din befattning</p>
            <p>
              <select id="yrk" onchange="doSum(this.form)">
                <option selected="selected" value="0">Yrk-klass</option>
                <option value="75">Y-klass 1</option>
                <option value="85">Y-klass 2</option>
                <option value="90">Y-klass 3</option>
                <option value="95">Y-klass 4</option>
                <option value="100">Y-klass 5</option>
                <option value="105">Y-klass 6</option>
                <option value="110">Y-klass 7</option>
                <option value="125">Y-klass 8</option>
                <option value="130">Y-klass 9</option>
                <option value="130">Y-klass 10</option>
              </select>
            </p>
            <input id="kanPlus" type="checkbox" />
            <span><strong>Kan+</strong></span>
          </td>
          <td>
            <strong>2 Mångkunnigheter</strong>
            <table>
              <tbody>
                <tr>
                  <td>
                    <select
                      id="selectMk1"
                      name="ru1"
                      onchange="onMkGroupChange(this, 'selectMk1Task', MkList);"
                    >
                      <option value="null">Mk1</option>
                      <option value="0">Sliphall</option>
                      <option value="1">Förråd</option>
                      <option value="2">Underhåll</option>
                      <option value="3">Grovplåt</option>
                      <option value="4">Delning</option>
                      <option value="5">Betlinje</option>
                      <option value="6">Terminalen</option>
                      <option value="7">Höglager</option>
                      <option value="8">Prov verkstad</option>
                      <option value="9">LP-produktion</option>
                      <option value="10">LP-Underhåll</option>
                      <option value="11">LP-Storfors</option>
                      <option value="12">Vaktstugan</option>
                      <option value="13">PSC-Nordic</option>
                    </select>
                  </td>
                  <td>
                    <select
                      id="selectMk1Task"
                      disabled="disabled"
                      onchange="onMkChange(this, 'inputMk1');"
                    >
                      <option value="null">Ingen vald</option>
                    </select>
                  </td>
                  <td><input id="inputMk1" name="ru7" type="text" /></td>
                </tr>
                <tr>
                  <td>
                    <select
                      id="selectMk2"
                      name="ru2"
                      onchange="onMkGroupChange(this, 'selectMk2Task', MkList);"
                    >
                      <option value="null">Mk2</option>
                      <option value="0">Sliphall</option>
                      <option value="1">Förråd</option>
                      <option value="2">Underhåll</option>
                      <option value="3">Grovplåt</option>
                      <option value="4">Delning</option>
                      <option value="5">Betlinje</option>
                      <option value="6">Terminalen</option>
                      <option value="7">Höglager</option>
                      <option value="8">Prov verkstad</option>
                      <option value="9">LP-produktion</option>
                      <option value="10">LP-Underhåll</option>
                      <option value="11">LP-Storfors</option>
                      <option value="12">Vaktstugan</option>
                      <option value="13">PSC-Nordic</option>
                    </select>
                  </td>
                  <td>
                    <select
                      id="selectMk2Task"
                      disabled="disabled"
                      onchange="onMkChange(this, 'inputMk2');"
                    >
                      <option value="null">Ingen vald</option>
                    </select>
                  </td>
                  <td><input id="inputMk2" name="ru8" type="text" /></td>
                </tr>
                <tr>
                  <td>
                    <select
                      id="selectMk3"
                      name="ru3"
                      onchange="onMkGroupChange(this, 'selectMk3Task', MkList);"
                    >
                      <option value="null">MK3</option>
                      <option value="0">Sliphall</option>
                      <option value="1">Förråd</option>
                      <option value="2">Underhåll</option>
                      <option value="3">Grovplåt</option>
                      <option value="4">Delning</option>
                      <option value="5">Betlinje</option>
                      <option value="6">Terminalen</option>
                      <option value="7">Höglager</option>
                      <option value="8">Prov verkstad</option>
                      <option value="9">LP-produktion</option>
                      <option value="10">LP-Underhåll</option>
                      <option value="11">LP-Storfors</option>
                      <option value="12">Vaktstugan</option>
                      <option value="13">PSC-Nordic</option>
                    </select>
                  </td>
                  <td>
                    <select
                      id="selectMk3Task"
                      disabled="disabled"
                      onchange="onMkChange(this, 'inputMk3');"
                    >
                      <option value="null">Ingen vald</option>
                    </select>
                  </td>
                  <td><input id="inputMk3" name="ru9" type="text" /></td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
    </table>

    <table>
      <tbody>
        <tr>
          <td>
            <strong>3 Utv.tillägg</strong>
            <p>Tid i % som görs utanför IF Metall</p>
            <p>
              <select id="in_utv" onchange="doSum(this.form)">
                <option selected="selected" value="0">- Inget -</option>
                <option value="20">> 5 %</option>
                <option value="25">> 10 %</option>
                <option value="50">> 20 %</option>
                <option value="75">> 30 %</option>
                <option value="100">> 40 %</option>
                <option value="125">> 50 %</option>
              </select>
            </p>
          </td>
          <td>
            <strong>4a Utb.allmän</strong>
            <p>Vilken utbildning som bef. kräver</p>
            <p>
              <select id="in_utb" onchange="doSum(this.form)">
                <option selected="selected" value="0">- Välj -</option>
                <option value="50">Operatör</option>
                <option value="75">Yrk-utbildning</option>
              </select>
            </p>
          </td>
          <td>
            <strong>4b Utb.special</strong>
            <p>Riktade olika utb med (licens mm)</p>
            <p>
              <select id="in_sutbp" onchange="doSum(this.form)">
                <option selected="selected" value="0">- Inget -</option>
                <option value="15">1 utb-vecka</option>
                <option value="30">2 utb-vecka</option>
                <option value="45">3 utb-vecka</option>
              </select>
            </p>
          </td>
          <td></td>
        </tr>
        <tr>
          <td>
            <p><strong>5 Samordn</strong></p>
            <p>Vilken nivå som samordnare</p>
            <p>
              <select id="in_sam" onchange="doSum(this.form)">
                <option selected="selected" value="0">- Inget -</option>
                <option value="50">Ej utbildad</option>
                <option value="100">Är utbildad</option>
                <option value="121">Gör alla krav</option>
              </select>
            </p>
          </td>
          <td>
            <p><strong>6 Bef. inriktning</strong></p>
            <p>Befattningen är direkt förhandlad mot</p>
            <p>
              <select id="in_befi" onchange="doSum(this.form)">
                <option selected="selected" value="0">- Inget -</option>
                <option value="75">Stödfunktioner</option>
                <option value="150">Prod-Underhåll</option>
              </select>
            </p>
          </td>
          <td>
            <strong>7 Special.tillägg</strong>
            <p>Används specialist kunskapen</p>
            <p>
              <select id="in_spec" onchange="doSum(this.form)">
                <option selected="selected" value="0">- Inget -</option>
                <option value="75">Ibland</option>
                <option value="90">Ofta</option>
                <option value="110">Alltid</option>
              </select>
            </p>
          </td>
          <td>
            <span><b>IV poäng</b></span
            ><input id="ti_ivp" type="text" />
          </td>
        </tr>
      </tbody>
    </table>

    <form name="formular">
      <div>
        &nbsp;
        <div>
          &nbsp;
          <table>
            <tbody>
              <tr>
                <td></td>
                <td><b>Y-erf</b></td>
                <td><strong>Mk</strong></td>
                <td><b>Utv</b></td>
              </tr>
              <tr>
                <td><span>Poäng</span></td>
                <td>
                  <span><input id="ti_yrkp" size="5" type="text" /></span>
                </td>
                <td>
                  <span><input id="ti_mkkr" size="5" type="text" /></span>
                </td>
                <td>
                  <span><input id="ti_utvp" size="5" type="text" /></span>
                </td>
              </tr>
              <tr>
                <td><span>Kr/mån</span></td>
                <td>
                  <span><input id="ti_yrkk" type="text" /></span>
                </td>
                <td>
                  <span><input id="ti_mkk" type="text" /></span>
                </td>
                <td>
                  <span><input id="ti_utvk" type="text" /></span>
                </td>
              </tr>
            </tbody>
          </table>
          <table>
            <tbody>
              <tr>
                <td></td>
                <td>
                  <span><strong>Allm utb</strong></span>
                </td>
                <td>
                  <span><b>Spec</b><strong>utb</strong></span>
                </td>
                <td>
                  <b><span>Sam ord </span></b>
                </td>
              </tr>
              <tr>
                <td><span>Poäng</span></td>
                <td>
                  <span><input id="ti_utb" type="text" /></span>
                </td>
                <td>
                  <span><input id="ti_sutbp" type="text" /></span>
                </td>
                <td>
                  <span><input id="ti_sam" type="text" /></span>
                </td>
              </tr>
              <tr>
                <td><span>Kr/mån</span></td>
                <td>
                  <span><input id="ti_utbk" type="text" /></span>
                </td>
                <td>
                  <span><input id="ti_sutbk" type="text" /></span>
                </td>
                <td>
                  <span><input id="ti_samk" type="text" /></span>
                </td>
              </tr>
            </tbody>
          </table>
          <table>
            <tbody>
              <tr>
                <td></td>
                <td><b>Bef inrikt</b></td>
                <td><b>Spec </b></td>
              </tr>
              <tr>
                <td><span>Poäng</span></td>
                <td>
                  <span><input id="ti_befi" type="text" /></span>
                </td>
                <td>
                  <span><input id="ti_spec" type="text" /></span>
                </td>
              </tr>
              <tr>
                <td><span>Kr/mån</span></td>
                <td>
                  <span><input id="ti_befik" type="text" /></span>
                </td>
                <td>
                  <span><input id="ti_speck" type="text" /></span>
                </td>
              </tr>
            </tbody>
          </table>
          &nbsp;
        </div>

        &nbsp;
        <table>
          <tbody>
            <tr>
              <td>
                <span><b>Av-del</b></span>
              </td>
              <td>
                <span><b>IV-del</b></span>
              </td>
            </tr>
            <tr>
              <td>
                <span><input id="ti_av" type="text" /></span>
              </td>
              <td>
                <span><input id="ti_ivk" type="text" /></span>
              </td>
            </tr>
          </tbody>
        </table>
        <table>
          <tbody>
            <tr>
              <td>
                <span><b>Blir Lön</b></span>
              </td>
              <td>
                <span><b>OB</b></span>
              </td>
              <td>
                <span><b>Inkomst</b></span>
              </td>
            </tr>
            <tr>
              <td>
                <span><input id="ti_aviv" type="text" /></span>
              </td>
              <td>
                <span><input id="ti_ob" type="text" /></span>
              </td>
              <td>
                <span><input id="ti_tot" type="text" /></span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </form>
  </div>

  <script language="JavaScript">
    jQuery("#run").click(function () {
      countSum();
    });
    var MkList = [
      [
        "Sliphall",
        0,
        [
          ["Slipare)", 40],
          ["Skärare", 44],
          ["Utleveranskontroll", 40],
          ["Synare/infraktare", 40],
          ["Synare)", 40],
          ["Produktionstekniker", 50],
          ["Plats.tekniker", 50],
        ],
      ],
      [
        "Förråd",
        1,
        [
          ["Förråd-mekaniker", 46],
          ["Förråd-turbil", 36],
        ],
      ],
      [
        "Underhåll",
        2,
        [
          ["El Akutgrupp", 50],
          ["El Media", 50],
          ["El Autmationstekniker", 50],
          ["Mek Akutgrupp", 48],
          ["Travers", 46],
          ["Lyftredskap", 46],
          ["Travers El-PLC", 50],
          ["Maskinarbetare", 48],
          ["Hydraulik", 50],
          ["Pumpar", 50],
          ["Media-mek", 46],
          ["Elektro-mek", 48],
          ["Yttre miljö", 42],
          ["Produktions Mek", 50],
          ["Brandman", 46],
        ],
      ],
      [
        "Grovplåt",
        3,
        [
          ["Reglerare", 48],
          ["Stegbalkvärmare", 48],
          ["Synare/infraktare", 44],
          ["Inläggare", 38],
          ["Grängesugnar", 46],
          ["Planare", 40],
          ["Maskinist", 44],
          ["Rullugnsoperatör", 48],
          ["SHRP flöde", 48],
          ["Ugns service", 48],
          ["Valsslip/montör", 46],
          ["Platstekniker", 50],
          ["Valsslip", 46],
          ["El Valsverk Sliphall", 50],
          ["Mek verkstad ", 46],
          ["Produktionstekniker", 50],
        ],
      ],
      [
        "Delning",
        4,
        [
          ["Rullsax", 44],
          ["Tvärsax", 44],
          ["P2 Plasma", 44],
          ["Hyvel", 46],
          ["Avestafräsen", 46],
          ["Foglinje", 46],
          ["Handfräs", 46],
          ["Returstålshantering", 40],
          ["Verktygsmakare-Tekniker", 46],
          ["Materialman skepp 4-5", 38],
          ["Plasma skepp 4-5", 44],
          ["Materialman", 38],
          ["Ursvik/Skärning", 46],
          ["Plats tekniker", 50],
          ["El Delning/Terminal", 50],
        ],
      ],
      [
        "Betlinje",
        5,
        [
          ["Påläggare", 38],
          ["Blästring/Planing", 42],
          ["Betning", 44],
          ["Lineoperatör", 38],
          ["Utlastare", 38],
          ["Vågoperatör", 40],
          ["Vågtravers", 38],
          ["Karbetning", 44],
          ["Neutralisering", 44],
          ["Kvalitets kordinator", 50],
          ["Plats tekniker", 50],
          ["EL Betning", 50],
        ],
      ],
      [
        "Terminalen",
        6,
        [
          ["Matrialman", 40],
          ["Kontrollantsyn", 40],
          ["Bandslipning", 42],
        ],
      ],
      [
        "Höglager",
        7,
        [
          ["Serviceman", 40],
          ["Matrialmottagare", 42],
          ["Packning", 44],
          ["Terminal utlastning", 40],
          ["Bocklastare", 40],
          ["Motviktstruck", 40],
          ["Plats tekniker", 50],
        ],
      ],
      [
        "Prov verkstad",
        8,
        [
          ["Delning", 46],
          ["Maskinbearbetning", 46],
          ["Mek.förstörande provning", 46],
          ["Mesta hallen", 48],
        ],
      ],
      [
        "LP-produktion",
        9,
        [
          ["Värmare", 46],
          ["Tångtravers", 42],
          ["Huvugn kallsåg", 46],
          ["Ställare", 48],
          ["Reglerare par 1", 48],
          ["Valsverksskötare", 46],
          ["Reglerare par 2-3", 48],
          ["Varmsågare", 42],
          ["Svalbädd", 40],
          ["Uppläggare", 42],
          ["Synare-Ultraljudare", 44],
          ["Packare/synare", 42],
          ["Provsåg", 42],
          ["Ultraljudare Lev 2", 48],
          ["Slipare/synare", 40],
          ["Truckförare", 40],
          ["Bocklastförare", 40],
          ["Syning/utlastn adm", 38],
          ["Skalsvarv", 44],
          ["Produktionstekniker", 50],
        ],
      ],
      [
        "LP-Underhåll",
        10,
        [
          ["Mekaniker", 46],
          ["Elektriker", 50],
          ["Valsverk/Miljötekniker", 46],
        ],
      ],
      [
        "LP-Storfors",
        11,
        [
          ["Värmebehandling/riktning", 46],
          ["Mekaniker", 46],
        ],
      ],
      ["Vaktstugan", 12, [["Bevakning", 40]]],
      [
        "PSC-Nordic",
        13,
        [
          ["Vattenskärning", 42],
          ["Plasma operatör", 44],
          ["Underhåll", 46],
          ["Kantpress 2000", 48],
          ["Svetsning", 48],
          ["Fogberedning", 46],
          ["Packning", 40],
          ["Avsändning", 40],
          ["Truck,lager", 40],
          ["Kantpress 1250", 48],
          ["Rikt/fogberedning", 44],
        ],
      ],
    ];
    var av = 0;
    var yrkp = 0;
    var ykk = 0;
    var point = parseFloat("<?= $point; ?>");
    var mkp = 0;
    var mkk = 0;
    var utvp = 0;
    var utvk = 0;
    var utbp = 0;
    var utbk = 0;
    var sutbp = 0;
    var sutbk = 0;
    var samp = 0;
    var samk = 0;
    var befip = 0;
    var befik = 0;
    var specp = 0;
    var speck = 0;
    var ivk = 0;
    var tot = 0;
    var obers = 0;
    var aviv = 0;
    
    function reseta() {
      avdel = 0;
      av = 0;
      yklass = 0;
      befkr = 0;
      yrkp = 0;
      ykk = 0;
      point = "<?= $point; ?>";;
      mkp = 0;
      mkk = 0;
      utvp = 0;
      utvk = 0;
      utbp = 0;
      utbk = 0;
      sutbp = 0;
      sutbk = 0;
      samp = 0;
      samk = 0;
      befip = 0;
      befik = 0;
      specp = 0;
      speck = 0;
      ivk = 0;
      tot = 0;
      obers = 0;
      aviv = 0;
    }

    function doSum(form) {
      av = parseInt(document.getElementById("avdel").value, 10);
      yrkp = parseInt(document.getElementById("yrk").value, 10);
      utvp = parseInt(document.getElementById("in_utv").value, 10);
      utbp = parseInt(document.getElementById("in_utb").value, 10);
      sutbp = parseInt(document.getElementById("in_sutbp").value, 10);
      samp = parseInt(document.getElementById("in_sam").value, 10);
      befip = parseInt(document.getElementById("in_befi").value, 10);
      specp = parseInt(document.getElementById("in_spec").value, 10);
      obers = parseInt(document.getElementById("skift").value, 10);
    }

    function countSum() {
      mkp = 0;
      var mkElementIds = ["selectMk1Task", "selectMk2Task", "selectMk3Task"];
      for (var i = 0; i < mkElementIds.length; ++i) {
        var mk = document.getElementById(mkElementIds[i]);
        var mkValue = parseInt(mk.value, 10);
        if (!isNaN(mkValue)) {
          mkp = mkp + mkValue;
        }
      }
      if (document.getElementById("kanPlus").checked) {
        yrkp = yrkp + 30;
      }
      ykk = yrkp * point;
      mkk = mkp * point;
      utvk = utvp * point;
      utbk = utbp * point;
      sutbk = sutbp * point;
      samk = samp * point;
      befik = befip * point;
      speck = specp * point;
      ivp = yrkp + mkp + utbp + utvp + sutbp + samp + befip + specp;
      ivk = ivp * point;
      tot = av + ivk + obers;
      document.getElementById("ti_yrkp").value = format_valkr(yrkp);
      document.getElementById("ti_yrkk").value = format_valkr(ykk);
      document.getElementById("ti_ivp").value = format_valkr(ivp);
      document.getElementById("ti_mkkr").value = format_valkr(mkp);
      document.getElementById("ti_utvp").value = format_valkr(utvp);
      document.getElementById("ti_utb").value = format_valkr(utbp);
      document.getElementById("ti_sutbp").value = format_valkr(sutbp);
      document.getElementById("ti_sam").value = format_valkr(samp);
      document.getElementById("ti_befi").value = format_valkr(befip);
      document.getElementById("ti_spec").value = format_valkr(specp);
      document.getElementById("ti_ivk").value = format_valkr(ivk);
      document.getElementById("ti_mkk").value = format_valkr(mkk);
      document.getElementById("ti_utvk").value = format_valkr(utvk);
      document.getElementById("ti_utbk").value = format_valkr(utbk);
      document.getElementById("ti_sutbk").value = format_valkr(sutbk);
      document.getElementById("ti_samk").value = format_valkr(samk);
      document.getElementById("ti_befik").value = format_valkr(befik);
      document.getElementById("ti_speck").value = format_valkr(speck);
      document.getElementById("ti_ob").value = format_valkr(obers);
      document.getElementById("ti_aviv").value = format_valkr(av + ivk);
      document.getElementById("ti_av").value = format_valkr(av);
      document.getElementById("ti_ivk").value = format_valkr(ivk);
      document.getElementById("ti_tot").value = format_valkr(tot);
    }
    function format_valkr(number, precision) {
      if (precision === undefined) {
        precision = 0;
      }
      groupingSize = 0;
      formated = "";
      integral = Math.floor(number).toString();
      for (index = integral.length; index > 0; --index) {
        formated = integral.charAt(index - 1) + formated;
        ++groupingSize;
        if (groupingSize == 3 && index > 0) {
          formated = " " + formated;
          groupingSize = 0;
        }
      }
      decimal = Math.round(
        Math.pow(10, precision) * (number - Math.floor(number))
      ).toString();
      while (decimal.length < precision) {
        decimal = "0" + decimal;
      }
      if (precision > 0) {
        return formated + "," + decimal;
      }
      return formated;
    }
    function initialize(elementIds, theList) {
      for (var i = 0; i < elementIds.length; ++i) {
        var element = document.getElementById(elementIds[i]);
        if (element === undefined) {
          alert(
            "error: HTML error, no " + elementIds[i] + " element in page."
          );
        }
        populate(element, theList);
      }
    }
    function populate(selectElement, optionList) {
      clear(selectElement, false);
      for (var i = 0; i < optionList.length; ++i) {
        var option = document.createElement("option");
        option.text = optionList[i][0];
        option.value = optionList[i][1];
        selectElement.add(option);
      }
    }
    function clear(selectElement, disabled) {
      selectElement.disabled = disabled;
      while (selectElement.length > 1) {
        selectElement.remove(1);
      }
    }
    function onMkGroupChange(owner, optionListId, dataStructure) {
      if (owner === undefined) {
        alert("error: onMkChange(owner), parameter is undefined.");
        return;
      }
      var items = document.getElementById(optionListId);
      if (items === null) {
        alert("error: No '" + optionListId + "' element.");
        return;
      }
      for (var i = 0; i < dataStructure.length; ++i) {
        if (dataStructure[i][1] == owner.value) {
          populate(items, dataStructure[i][2]);
          return;
        }
      }
      clear(items, true);
      items.value = "null";
      if ("createEvent" in document) {
        var evt = document.createEvent("HTMLEvents");
        evt.initEvent("change", false, true);
        items.dispatchEvent(evt);
      } else {
        items.fireEvent("onchange");
      }
    }
    function onMkChange(owner, inputElementId) {
      if (owner === undefined) {
        alert("error: selectItem_OnChange(owner), parameter is undefined.");
        return;
      }
      var input = document.getElementById(inputElementId);
      if (input === null) {
        alert("error: No '" + inputElementId + "' element.");
        return;
      }
      var option = owner.options[owner.selectedIndex];
      var value = parseInt(option.value);
      if (isNaN(value)) {
        input.value = "";
      } else {
        input.value = option.value;
      }
    }
  </script>
  
<?php
  return ob_get_clean();
}
  
add_shortcode('rakna_loen', 'rakna_loen_shortcode');


function rakna_basbelopp_shortcode() {
  ob_start();

  $basbeloppet = get_field( 'basbeloppet', 'options' );
  ?>
 <div align="center">
      &nbsp;
      <table id="AutoNumber1" border="0" cellspacing="1" cellpadding="0">
        <tbody>
          <tr>
            <td>
              <span style="color: #000080; font-family: Arial; font-size: small"
                >Inom Metalls Järnbruks klubb har vi en försäkring som börjar
                gälla efter 90:e sjukdagen. Vår sjukförsäkring innebär att vi
                betalar en premie till Folksam varje månad och när en Metallare
                blir långtidssjuk så kontaktar vi Folksam som då ser till att du
                får ut en skattefri månadsersättning. </span
              ><span style="color: #000080">
                <strong
                  ><span style="font-family: Arial; font-size: small">
                    Hur ser dina inkomster ut och var får du ersättning ifrån
                    och under vilka tidsperioder ser du nedan :
                  </span></strong
                ></span
              >
              <strong
                ><span
                  style="color: #000080; font-family: Arial; font-size: small"
                >
                  ------------------------------------------------------------------------------------------------------------------------------------</span
                ></strong
              >
            </td>
          </tr>
        </tbody>
      </table>
      &nbsp;
    </div>
    <div id="basbeloppet" gati="<?php echo $basbeloppet; ?>" align="center">
      <b>
        <span style="color: #000080; font-family: Arial; font-size: small"
          >Ange din Månadsinkomst:</span
        ><span style="color: #000000; font-family: Arial"> </span></b
      >
      <input id="manlon" size="10" type="text" value="25000" /><b
        ><button id="run">Beräkna</button><button id="reset" type="reset">Rensa</button></b
      >
    </div>
    <div align="center">
      <table class="styled-table">
        <thead>
          <tr class="active-row">
            <td>
              <span style="color: #000080; font-size: small">Dagarbetare</span>
            </td>
            <td>
              <b
                ><span
                  style="color: #000080; font-family: Arial; font-size: small"
                  >Dag 1 </span
                ></b
              >
            </td>
            <td>
              <b
                ><span
                  style="color: #000080; font-family: Arial; font-size: small"
                  >Dag 2-14</span
                ></b
              >
            </td>
            <td>
              <b
                ><span
                  style="color: #000080; font-family: Arial; font-size: small"
                  >Dag 15 - 3 mån</span
                ></b
              >
            </td>
            <td>
              <b
                ><span
                  style="color: #000080; font-family: Arial; font-size: small"
                  >3 mån -1 år</span
                ></b
              >
            </td>
            <td>
              <b
                ><span
                  style="color: #000080; font-family: Arial; font-size: small"
                  >1 år - 2 år</span
                ></b
              >
            </td>
          </tr>
        </thead>
        <tbody>
          <tr class="active-row">
            <td>
              <b
                ><span
                  style="color: #800000; font-family: Arial; font-size: small"
                  >Karensdagen</span
                ></b
              >
            </td>
            <td align="center" id="ti_z0" ></td>
            <td align="center" bgcolor="#C0C0C0">
              <b><span style="font-family: Arial">---</span></b>
            </td>
            <td align="center" bgcolor="#C0C0C0">
              <b><span style="font-family: Arial">---</span></b>
            </td>
            <td align="center" bgcolor="#C0C0C0">
              <b><span style="font-family: Arial">---</span></b>
            </td>
            <td align="center" bgcolor="#C0C0C0">
              <b><span style="font-family: Arial">---</span></b>
            </td>
          </tr>
          <tr class="active-row">
            <td align="left">
              <b>
                <span
                  style="color: #800000; font-family: Arial; font-size: small"
                  >Sjuklön företaget</span
                ></b
              >
            </td>
            <td align="center" bgcolor="#C0C0C0">
              <b><span style="font-family: Arial">---</span></b>
            </td>
            <td align="center" ti_a1></td>
            <td align="center" bgcolor="#C0C0C0">
              <b><span style="font-family: Arial">---</span></b>
            </td>
            <td align="center" bgcolor="#C0C0C0">
              <b><span style="font-family: Arial">---</span></b>
            </td>
            <td align="center" bgcolor="#C0C0C0">
              <b><span style="font-family: Arial">---</span></b>
            </td>
          </tr>
          <tr class="active-row">
            <td align="left">
              <b
                ><span
                  style="color: #800000; font-family: Arial; font-size: small"
                  >F-kassan</span
                ></b
              >
            </td>
            <td align="center" bgcolor="#C0C0C0">
              <b><span style="font-family: Arial">---</span></b>
            </td>
            <td align="center" bgcolor="#C0C0C0">
              <b><span style="font-family: Arial">---</span></b>
            </td>
            <td align="center" id="ti_b2"></td>
            <td align="center" id="ti_b3"></td>
            <td align="center" id="ti_c2"></td>
          </tr>
          <tr class="active-row">
            <td align="left">
              <b
                ><span
                  style="color: #800000; font-family: Arial; font-size: small"
                  >AGS-försäkring</span
                ></b
              >
            </td>
            <td align="center" bgcolor="#C0C0C0">
              <b><span style="font-family: Arial">---</span></b>
            </td>
            <td align="center" bgcolor="#C0C0C0">
              <b><span style="font-family: Arial">---</span></b>
            </td>
            <td align="center" id="ti_c3"></td>
            <td align="center"id="ti_c6"></td>
            <td align="center" bgcolor="#C0C0C0">
              <b><span style="font-family: Arial">---</span></b>
            </td>
          </tr>
          <tr class="active-row">
            <td align="left">
              <b
                ><span
                  style="color: #0000ff; font-family: Arial; font-size: small"
                  >Sjuk/efterlev.förs* </span
                ></b
              >
            </td>
            <td align="center" bgcolor="#C0C0C0">
              <b><span style="font-family: Arial">---</span></b>
            </td>
            <td align="center" bgcolor="#C0C0C0">
              <b><span style="font-family: Arial">---</span></b>
            </td>
            <td align="center" bgcolor="#C0C0C0">
              <b><span style="font-family: Arial">---</span></b>
            </td>
            <td align="center" bgcolor="#C0C0C0">
              <b><span style="font-family: Arial">---</span></b>
            </td>
            <td align="center" id="ti_d7"></td>
          </tr>
          <tr class="active-row">
            <td align="left">
              <b
                ><span
                  style="color: #0000ff; font-family: Arial; font-size: small"
                  >JBK:s försäkring*</span
                ></b
              >
            </td>
            <td align="center" bgcolor="#C0C0C0">
              <b><span style="font-family: Arial">---</span></b>
            </td>
            <td align="center" bgcolor="#C0C0C0">
              <b><span style="font-family: Arial">---</span></b>
            </td>
            <td align="center" bgcolor="#C0C0C0">
              <b><span style="font-family: Arial">---</span></b>
            </td>
            <td align="center"id="ti_d2"></td>
            <td align="center" id="ti_d6"></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div align="center">
      <table border="1" width="100%" cellspacing="1">
        <tbody>
          <tr>
            <td valign="top">
              <b>
                <span
                  style="
                    color: #800000;
                    font-family: Arial;
                    font-size: xx-small;
                  "
                  >Sjuklön företaget</span
                ></b
              >
            </td>
            <td>
              <p align="left">
                <span
                  style="color: #000080; font-family: Arial; font-size: small"
                  >För den första dagen, tom dag 15 har man rätt till ca 80 %
                  sjuklön från företaget, man får ett timlöneavdrag,
                  <a
                    href="https://jbk.mxds.se/loner/manlon/avdragen.htm"
                    target="_blank"
                    rel="noopener"
                    >du kan kolla in mera här</a
                  ></span
                >
              </p>
            </td>
          </tr>
          <tr>
            <td valign="top">
              <b
                ><span
                  style="
                    color: #800000;
                    font-family: Arial;
                    font-size: xx-small;
                  "
                >
                  F-kassan</span
                ></b
              >
            </td>
            <td>
              <p align="left">
                <span
                  style="color: #000080; font-family: Arial; font-size: small"
                  >Vid beräkning av sjukpenning ska den sjukpenninggrundande
                  inkomsten först multipliceras med talet 0,97. På
                  <b> detta</b> underlag betalas 80 % i sjukpenning, men då max
                  upp till 7.5  basbelopp
                  <a
                    href="https://jbk.mxds.se/forsakringar/raknabasbelopp.htm"
                    target="_blank"
                    rel="noopener"
                    >du kan se mer om basbelopp här</a
                  ></span
                >
              </p>
            </td>
          </tr>
          <tr>
            <td valign="top">
              <b
                ><span
                  style="
                    color: #800000;
                    font-family: Arial;
                    font-size: xx-small;
                  "
                >
                  AGS-försäkring</span
                ></b
              >
            </td>
            <td>
              <p align="left">
                <span
                  style="color: #000080; font-family: Arial; font-size: small"
                  >AGS börjar gälla när man har varit anställd i minst 90
                  dagar.  Även tidigare anställningar kan räknas in i denna s k
                  kvalifikationstid . Den tidigare anställningen får inte ligga
                  längre tillbaka än två år räknat från den dag då den pågående
                  anställningen började.  Förutom att fullgöra
                  kvalifikationstiden måste man ha fått sjukpenninggrundande
                  inkomst (SGI) fastställd av försäkringskassan  och ha varit
                  frisk till minst 25% sedan minst en vecka innan försäkringen
                  träder i kraft.
                  <a
                    href="https://jbk.mxds.se/forsakringar/AGS/agsers.htm"
                    target="_blank"
                    rel="noopener"
                    >du kan läsa mer om AGS här</a
                  >
                </span>
              </p>
            </td>
          </tr>
          <tr>
            <td valign="top">
              <b
                ><span
                  style="
                    color: #800000;
                    font-family: Arial;
                    font-size: xx-small;
                  "
                >
                  Sjuk/efterlevande försäkringen</span
                ></b
              >
            </td>
            <td>
              <p align="left">
                <span
                  style="color: #000080; font-family: Arial; font-size: small"
                  >Minst halv arbetsoförmåga i 360 sammanhängande dagar (karens)
                  Vid perioder av full arbetsförhet på vardera högst 14 dagar
                  ska beräkning av karens ej avbrytas. Månadsersättning betalas
                  ut med 1/30 av försäkringsbeloppet för varje kalenderdag vid
                  arbetsoförmåga i max 365 dagar Avbruten ersättningsperiod kan
                  återupptas om man inom ett år åter blir arbetsoförmögen i mer
                  än 14 dagar i följd</span
                >
              </p>
            </td>
          </tr>
          <tr>
            <td valign="top">
              <b
                ><span
                  style="
                    color: #800000;
                    font-family: Arial;
                    font-size: xx-small;
                  "
                >
                  JBK:s försäkring</span
                ></b
              >
            </td>
            <td>
              <p align="left">
                <span
                  style="color: #000080; font-family: Arial; font-size: small"
                >
                  <strong>
                    JBK:s sjukförsäkring gäller efter 3 månader. Vi betalar en
                    premie varje månad och när en medlem blir långtidssjuk
                    kontaktar vi Folksam som ser till att du får en skattefri
                    månadsersättning.</strong
                  ></span
                >
              </p>
            </td>
          </tr>
        </tbody>
      </table>
      &nbsp;
    </div>
    <h5 align="center">
      <b
        ><span style="color: #0000ff; font-family: Arial; font-size: small"
          >* Ersättningen är skattefri .
        </span></b
      >
    </h5>
    <script>
      jQuery("#run").click(function () {
        countSum();
      });

      var basbelopp = 45000//parseInt(document.getElementById("basbeloppet").attributes[1].value);
            console.log(basbelopp);
    console.log(document.getElementById("manlon").value);
    var bastak = (basbelopp * 7.5) / 12;
    function countSum() {
      manlonen = parseInt(document.getElementById("manlon").value);
      if (isPosInteger(manlonen)) {
        sjuklon = ((manlonen * 0.8 * 12) / (52 * 40)) * 8;
        if (manlonen > bastak) {
          fk = bastak * 0.97 * 0.8;
        }
        if (manlonen < bastak) {
          fk = manlonen * 0.97 * 0.8;
        }
        karens = ((manlonen * 12) / (52 * 40)) * 8;
        skatt = 0;
      console.log(format_valkr(karens * -1))
        A1 = sjuklon;
        B2 = fk;
        B3 = B2 * 0.125;
        C2 = B2;
        C3 = B3;
        C6 = basbelopp * 0.02;
        D2 = B2;
        D6 = basbelopp * 0.02;
        D7 = 800;
        document.getElementById("ti_z0").innerHTML =
          format_valkr(karens * -1) + "/dag";
        document.getElementById("ti_a1").innerHTML =
          format_valkr(A1) + "/dag";
        document.getElementById("ti_b2").innerHTML =
          format_valkr(B2) + "/mån";
        document.getElementById("ti_b3").innerHTML =
          format_valkr(B3) + "/mån";
        document.getElementById("ti_c2").innerHTML =
          format_valkr(C2) + "/mån";
        document.getElementById("ti_c3").innerHTML =
          format_valkr(C3) + "/mån";
        document.getElementById("ti_c6").innerHTML =
          format_valkr(C6) + "/mån";
        document.getElementById("ti_d7").innerHTML =
          format_valkr(D7) + "/mån";
        document.getElementById("ti_d2").innerHTML =
          format_valkr(D2) + "/mån";
        document.getElementById("ti_d6").innerHTML =
          format_valkr(D6) + "/mån";
      }
      function format_valkr(number, precision) {
        precision = 2;
        group = 0;
        formated = "";
        integral = Math.floor(number).toString();
        for (index = integral.length; index > 0; index--) {
          formated = integral.charAt(index - 1) + formated;
          if (++group == 3 && index > 0) {
            formated = " " + formated;
            group = 0;
          }
        }
        decimal = Math.round(
          Math.pow(10, precision) * (number - Math.floor(number))
        ).toString();
        while (decimal.length < precision) decimal = "0" + decimal;
        return formated + " kr";
      }
    }
    function isPosInteger(inputVal) {
      if (inputVal) {
        inputStr = inputVal.toString();
        for (var i = 0; i < inputStr.length; i++) {
          var oneChar = inputStr.charAt(i);
          if (oneChar < "0" || oneChar > "9") {
            if (oneChar != ".") {
              alert("Var vänlig undvik andra tecken än siffror.");
              return false;
            }
          }
        }
      } else {
        alert("Var vänlig fyll i ett värde.");
        return false;
      }
      return true;
    }
  </script>

    <?php
    return ob_get_clean();
}
  
add_shortcode('rakna_basbelopp', 'rakna_basbelopp_shortcode');


function add_google_tag_script() {
    ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-XXWYBN4DVJ"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-XXWYBN4DVJ');
    </script>
    <?php
}
add_action('wp_head', 'add_google_tag_script');



function add_raknescript_to_specific_page_footer() {
  if ( is_page(1988) ) {
?>
  <script>

    function doSum(form) {
    var innan = new Array(7);
    innan[0] = document.getElementById("sd1").selectedIndex;
    innan[1] = document.getElementById("sd2").selectedIndex;
    innan[2] = document.getElementById("sd3").selectedIndex;
    innan[3] = document.getElementById("sd4").selectedIndex;
    innan[4] = document.getElementById("sd5").selectedIndex;
    innan[5] = document.getElementById("sd6").selectedIndex;
    innan[6] = document.getElementById("sd7").selectedIndex;
    var efter = new Array(7);
    efter[0] = document.getElementById("nd1").selectedIndex;
    efter[1] = document.getElementById("nd2").selectedIndex;
    efter[2] = document.getElementById("nd3").selectedIndex;
    efter[3] = document.getElementById("nd4").selectedIndex;
    efter[4] = document.getElementById("nd5").selectedIndex;
    efter[5] = document.getElementById("nd6").selectedIndex;
    efter[6] = document.getElementById("nd7").selectedIndex;
    var total_schema_tid = 0;
    var total_ny_tid = 0;
    var avvikande = 0;
    for (i = 0; i < 7; i++) {
    if (innan[i] != 3) total_schema_tid = total_schema_tid + 8;
    if (efter[i] != 3) {
    total_ny_tid = total_ny_tid + 8;
    if (efter[i] != innan[i]) avvikande = avvikande + 8;
    }
    }
    document.getElementById("t1").value = total_schema_tid.toString() + " tim";
    document.getElementById("t2").value = total_ny_tid.toString() + " tim";
    var overtid = total_ny_tid - total_schema_tid;
    if (overtid < 0) overtid = 0; var omstallning = avvikande - overtid; document.getElementById("t3").value = overtid.toString() + " tim"; document.getElementById("t4").value = omstallning.toString() + " tim"; var overers = overtid * 47.41; if (overtid == 0) document.getElementById("t5").value = ""; else document.getElementById("t5").value = format_valkr(overers, 2) + " kr"; var omsters = omstallning * 19.02; if (omstallning == 0) document.getElementById("t6").value = ""; else document.getElementById("t6").value = format_valkr(omsters, 2) + " kr"; document.getElementById("t7").value = format_valkr(overers + omsters, 2) + " kr"; } 

    function format_valkr(number, precision) { precision = 2; group = 0; formated = ""; integral = Math.floor(number).toString(); for (index = integral.length; index > 0; index--) {
    formated = integral.charAt(index - 1) + formated;
    if (++group == 3 && index > 0) {
    formated = " " + formated;
    group = 0;
    }
    }
    decimal = Math.round(
    Math.pow(10, precision) * (number - Math.floor(number))
    ).toString();
    while (decimal.length < precision) decimal = "0" + decimal;
    return formated + "," + decimal;
    }
  </script>

          <?php
      }
}
add_action('wp_footer', 'add_raknescript_to_specific_page_footer');