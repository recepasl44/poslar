<?php
/**
 * GurmePOS için işlemde kullanılacak özelliklerin standart haline getirilmesi için gerekli sınıf.
 *
 * @package GurmeHub
 */

/**
 * GPOS_Transaction_Utils sınıfı.
 */
class GPOS_Transaction_Utils {

	/**
	 * Entegre edilen platformlar.
	 */
	const WOOCOMMERCE        = 'woocommerce';
	const WOOCOMMERCE_SUBS   = 'woocommerce_subs';
	const WOOCOMMERCE_BLOCKS = 'woocommerce_blocks';
	const GIVEWP             = 'givewp';
	const GIVEWP_V3          = 'givewp_v3';
	const EDD                = 'edd';
	const NINJA_FORMS        = 'ninja_forms';
	const WPFORMS            = 'wpforms';
	const ELEMENTOR          = 'elementor';
	const ELEMENTOR_PRO      = 'elementor_pro';
	const LEARNPRESS         = 'learn_press';
	const THEEVENTSCALENDAR  = 'the_events_calendar';
	const PMPRO              = 'paid_memberships_pro';
	const TRAVELERWP         = 'travelerwp';
	const LATEPOINT          = 'latepoint';
	/**
	 * İşlem tipleri.
	 */
	const PAYMENT = 'payment';                                  // Ödeme işlemi.
	const REFUND  = 'refund';                                   // İade işlemi.
	const CANCEL  = 'cancel';                                   // İptal işlemi.

	/**
	 * İşlem durumları.
	 */
	const STARTED    = 'gpos_started';                          // İşlem başlatıldı.
	const REDIRECTED = 'gpos_redirected';                       // işlem Ödeme sayfasına yönlendirildi.
	const COMPLETED  = 'gpos_completed';                        // işlem tamamlandı.
	const PENDING    = 'gpos_pending_payment';                  // işlem ödeme bekliyor.
	const FAILED     = 'gpos_failed';                           // işlem hata ile karşılaştı.

	/**
	 * İşlem iade durumları.
	 */
	const REFUND_STATUS_CANCELLED        = 'gpos_refund_status_cancelled';          // İşlem iptal edildi.
	const REFUND_STATUS_NOT_REFUNDED     = 'gpos_refund_status_n_refunded';         // işlem iade edilmedi.
	const REFUND_STATUS_REFUNDED         = 'gpos_refund_status_refunded';           // işlem iade edildi.
	const REFUND_STATUS_PARTIAL_REFUNDED = 'gpos_refund_status_p_refunded';         // işlem hata parçalı iade edildi.

	/**
	 * İşlem satırı durumları.
	 */
	const LINE_NOT_REFUNDED     = 'gpos_line_n_refunded';      // Satır iade edilmedi.
	const LINE_REFUNDED         = 'gpos_line_refunded';          // Satır iade edildi.
	const LINE_PARTIAL_REFUNDED = 'gpos_line_p_refunded';  // Satır parçalı iade edildi.

	/**
	 * İşlemin güvenlik tipi.
	 */
	const THREED  = 'threed';                                   // 3D güvenlikli işlem.
	const REGULAR = 'regular';                                  // Güvenliksiz işlem.

	/**
	 * Log tipleri.
	 */
	const LOG_PROCESS_AUTH              = 'process_auth';                   // Session token, login gibi işlemler için log işlem tipi.
	const LOG_PROCESS_START_COMMON_FORM = 'process_start_common_form';      // Ortak ödeme sayfası başlatıldı.
	const LOG_PROCESS_START_ALTERNATIVE = 'process_start_alternative';      // Alternatif ödeme işlemi başlatıldı.
	const LOG_PROCESS_START_IFRAME      = 'process_start_iframe';           // iFrame ödeme işlemi başlatıldı.
	const LOG_PROCESS_START_REGULAR     = 'process_start_regular';          // Regular (3D'siz) işlemler için log işlem tipi.
	const LOG_PROCESS_START_3D          = 'process_start_3d';               // 3D başlangıç işlemi için log işlem tipi.
	const LOG_PROCESS_REDIRECT          = 'process_redirect';               // Ödeme sayfasına yönlendirme işlemi için log işlem tipi.
	const LOG_PROCESS_CALLBACK          = 'process_callback';               // Kuruluştan dönen 3D verisini için log işlem tipi.
	const LOG_PROCESS_NOTIFY            = 'process_notify';                 // Kuruluştan işlemler bittikten sonra dönen veri dönmesi.
	const LOG_PROCESS_FINISH            = 'process_finish';                 // Varsa 3D dönüşünde işlem kapatma için log işlem tipi.
	const LOG_PROCESS_CANCEL            = 'process_cancel';                 // İptal işlemi için log işlem tipi.
	const LOG_PROCESS_REFUND            = 'process_refund';                 // İade işlemi için log işlem tipi.
	const LOG_PROCESS_STATUS_CHECK      = 'process_check';                  // İşlem durum sorgulama.
	const LOG_PROCESS_SAVE_CARD         = 'process_save_card';              // Kart kayıt etme sorguları.

	/**
	 * Action Log Durum tipleri.
	 */
	const ACTION_LOG_STATUS_SEND_SUCCESS = 'process_send_success';           // Başarılı olma durumu.
	const ACTION_LOG_STATUS_SEND_FAIL    = 'process_send_fail';              // Başarısız olma durumu.

	/**
	 * Action Log İşlem tipleri.
	 */
	const ACTION_LOG_PROCESS_GSS_ADD_ROW       = 'process_gss_add_row';           // Google Sheet yeni satır ekleme.
	const ACTION_LOG_PROCESS_GSS_ADD_WORKSHEET = 'process_gss_add_worksheet';     // Google sheet yeni çalışma sayfası ekleme.
	const ACTION_LOG_PROCESS_WEBHOOK           = 'process_webhook';               // WebHook çağrısı yapma durumu

	/**
	 * Form tipleri.
	 */
	const FORM_TYPE_EMPTY = 'empty_form';
	const FORM_TYPE_CARD  = 'card_form';

	/**
	 * Kuruluş tiplerii.
	 */
	const PAYMENT_METHOD_TYPE_VIRTUAL_POS     = 'virtual_pos';
	const PAYMENT_METHOD_TYPE_COMMON          = 'common_form_payment';
	const PAYMENT_METHOD_TYPE_ALTERNATIVE     = 'alternative_payment';
	const PAYMENT_METHOD_TYPE_IFRAME          = 'iframe_payment';
	const PAYMENT_METHOD_TYPE_BANK_TRANSFER   = 'bank_transfer';
	const PAYMENT_METHOD_TYPE_SHOPPING_CREDIT = 'shopping_credit';
}
