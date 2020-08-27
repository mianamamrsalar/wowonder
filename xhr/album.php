<?php 
if ($f == 'album') {
    if ($s == 'create_album' && Wo_CheckSession($hash_id) === true) {
        if (empty($_POST['album_name'])) {
            $errors[] = $error_icon . $wo['lang']['please_check_details'];
        } else if (empty($_FILES['postSongs']['name'])) {
            $errors[] = $error_icon . $wo['lang']['please_check_details'];
        }
        if ($wo['config']['who_upload'] == 'pro' && $wo['user']['is_pro'] == 0 && !Wo_IsAdmin() && !empty($_FILES['postSongs'])) {
            $errors[] = $error_icon . $wo['lang']['free_plan_upload_pro'];
        }
        if (isset($_FILES['postSongs']['name'])) {
            $allowed = array(
                'mp3'
            );
           
                $new_string = pathinfo($_FILES['postSongs']['name']);
                if (!in_array(strtolower($new_string['extension']), $allowed)) {
                    $errors[] = $error_icon . $wo['lang']['please_check_details'];
                }
            
        }
        if (empty($errors)) {
            $post_data = array(
                'user_id' => Wo_Secure($wo['user']['user_id']),
                'album_name' => Wo_Secure($_POST['album_name']),
                'postPrivacy' => Wo_Secure(0),
                'time' => time()
            );
            if (!empty($_POST['id'])) {
                if (is_numeric($_POST['id'])) {
                    $post_data = array(
                        'album_name' => Wo_Secure($_POST['album_name'])
                    );
                    $id        = Wo_UpdatePostData($_POST['id'], $post_data);
                }
            } else {
                $id = Wo_RegisterPost($post_data);
            }
            if (count($_FILES['postSongs']['name']) > 0) {
                 
                    $fileInfo = array(
                        'file' => $_FILES["postSongs"]["tmp_name"],
                        'name' => $_FILES['postSongs']['name'],
                        'size' => $_FILES["postSongs"]["size"],
                        'type' => $_FILES["postSongs"]["type"],
                        'types' => 'mp3'
                    );
                    $file     = Wo_ShareFile($fileInfo, 1);
                    if (!empty($file)) {
                        //$media_album = Wo_RegisterAlbumMedia($id, $file['filename']);

                        if (!empty($file)) {
                            $media_album = Wo_RegisterAlbumMedia($id, $file['filename']);
                            $post_data['multi_image'] = 0;
                            $post_data['multi_image_post'] = 1;
                            $post_data['album_name'] = '';
                            $post_data['postFile'] = $file['filename'];
                            $post_data['postFileName'] = $file['name'];
                            $new_id = Wo_RegisterPost($post_data);
                            $media_album = Wo_RegisterAlbumMedia($new_id, $file['filename'],$id);
                        }



                    }
                
            }
            $data = array(
                'status' => 200,
                'href' => Wo_SeoLink('index.php?link1=post&id=' . $id)
            );
        }
        header("Content-type: application/json");
        if (isset($errors)) {
            echo json_encode(array(
                'errors' => $errors
            ));
        } else {
            echo json_encode($data);
        }
        exit();
    }
}
