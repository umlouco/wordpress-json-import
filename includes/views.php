<?php

class MF_Views {

    public function dashboard() {
        ?> 
        <div class="wrap">
            <h1 class="wp-heading-inline">JSON Import</h1>
            <hr class="wp-header-end">
            <form name="mf_file" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="control-label">JSON File</label>
                    <input type="file" name="mf_file" value="" class="form-control">
                </div>
                <div class="form-group">
                    <input type="submit" value="Upload" class="btn btn-primary">
                </div>
            </form>
        </div>
        <?php
    }

    public function error($text) {
        ?>
        <div class="alert alert-danger">
            <?php echo $text; ?>
        </div>
        <?php
    }

}
