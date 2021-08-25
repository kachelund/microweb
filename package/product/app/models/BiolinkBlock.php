<?php
/*
 * @copyright Copyright (c) 2021 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

namespace Altum\Models;

class BiolinkBlock extends Model {

    public function delete($biolink_block_id) {

        if(!$biolink_block = db()->where('biolink_block_id', $biolink_block_id)->getOne('biolinks_blocks')) {
            die();
        }

        /* Delete the stored files of the link, if any */
        if(in_array($biolink_block->type, ['image', 'image_grid'])) {
            $biolink_block->settings = json_decode($biolink_block->settings);

            /* Offload deleting */
            if(\Altum\Plugin::is_active('offload') && settings()->offload->uploads_url) {
                $s3 = new \Aws\S3\S3Client(get_aws_s3_config());
                $s3->deleteObject([
                    'Bucket' => settings()->offload->storage_name,
                    'Key' => 'uploads/block_images/' . $biolink_block->settings->image,
                ]);
            }

            /* Local deleting */
            else {
                /* Delete current file */
                if(!empty($biolink_block->settings->image) && file_exists(UPLOADS_PATH . 'block_images/' . $biolink_block->settings->image)) {
                    unlink(UPLOADS_PATH . 'block_images/' . $biolink_block->settings->image);
                }
            }
        }

        /* Delete the stored files of the link, if any */
        if(in_array($biolink_block->type, ['link', 'mail', 'vcard'])) {
            $biolink_block->settings = json_decode($biolink_block->settings);

            /* Offload deleting */
            if(\Altum\Plugin::is_active('offload') && settings()->offload->uploads_url) {
                $s3 = new \Aws\S3\S3Client(get_aws_s3_config());
                $s3->deleteObject([
                    'Bucket' => settings()->offload->storage_name,
                    'Key' => 'uploads/block_thumbnail_images/' . $biolink_block->settings->image,
                ]);
            }

            /* Local deleting */
            else {
                /* Delete current file */
                if(!empty($biolink_block->settings->image) && file_exists(UPLOADS_PATH . 'block_thumbnail_images/' . $biolink_block->settings->image)) {
                    unlink(UPLOADS_PATH . 'block_thumbnail_images/' . $biolink_block->settings->image);
                }
            }
        }

        /* Delete from database */
        db()->where('biolink_block_id', $biolink_block_id)->delete('biolinks_blocks');

        /* Clear the cache */
        \Altum\Cache::$adapter->deleteItemsByTag('biolinks_links_user_' . $biolink_block->user_id);
        \Altum\Cache::$adapter->deleteItemsByTag('link_id=' . $biolink_block->link_id);

    }
}
