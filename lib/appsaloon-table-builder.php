<?php


namespace wp_gdpr\lib;

/**
 * Class Appsaloon_Table_Builder
 * @package wp_gdpr\lib
 *
 * allows to build simple table
 */
class Appsaloon_Table_Builder {

	public $custom_classes;
	public $head;
	public $data;
	public $footer;

	/**
	 * Appsaloon_Table_Builder constructor.
	 */
	public function __construct( array $head, array $data, array $footer, string $custom_classes = 'wp-list-table widefat fixed striped' ) {
		$this->custom_classes = $custom_classes;
		$this->head           = $head;
		$this->data           = $data;
		$this->footer         = $footer;
	}

	/**
	 * table open tab
	 */
	public function open_table() {
		?><table class="<?php echo $this->custom_classes; ?>"><?php
	}

	/**
     * build head
     */
	public function build_head() {
		if ( empty( $this->head ) ) {
			return;
		}
		?>
        <thead>
        <tr>
			<?php foreach ( $this->head as $header ) : ?>
                <th><?php echo $header; ?></th>
			<?php endforeach; ?>
        </tr>
        </thead>
		<?php
	}


	/**
	 * show body
	 */
	public function build_body() {
		?>
        <tbody>
		<?php foreach ( $this->data as $rows ) : ?>
            <tr>
				<?php foreach ( $rows as $single_row ) : ?>
                    <td><?php  echo $single_row; ?></td>
				<?php endforeach; ?>
            </tr>
		<?php endforeach; ?>
        </tbody>
		<?php
	}

	/**
	 * simple footer
	 */
	public function build_footer() {
		if ( empty( $this->footer ) ) {
			return;
		}
		?>
        <tfoot>
        <tr>
			<?php foreach ( $this->footer as $footer ) : ?>
                <td><?php echo $footer; ?></td>
			<?php endforeach; ?>
        </tr>
        </tfoot>
		<?php
	}

	/**
	 * close tag of table
	 */
	public function close_table() {
		?></table><?php
	}

	/**
	 * show table
	 */
	public function print_table() {
        $this->open_table();
		$this->build_head();
		$this->build_body();
		$this->build_footer();
		$this->close_table();
	}
}