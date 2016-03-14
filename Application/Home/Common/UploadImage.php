<?php
namespace Home\Common;

/**
 *
 * 图片上传类
 * @author ChenYue
 *
 * @param $mageStauts                 图片上传状态 1为正常状态
 * @param $iamgePath                  图片上传成功保存在数据库的路径
 * @param $imagePathTemp              临时保存图片上传成功保存在数据库的路径
 * @param $destination_folder          上传文件路径
 * @param $imageName                  上传的图片名（可自定义）
 * @param $fileArray                  上传的图片信息数组
 * @param $updateImage                判断是否更新原有图片 0表示不更新 ， 1 表示更新
 * @param $uptypes                    支持上传的图片类型
 * @param max_file_size               支持上传的图片最大类型
 * @param imageType                   图片的类型
 *
 */
class UploadImage
{

    public $imageStauts = 1;
    public $iamgePath = '';
    private $imagePathTemp = "";
    private $destination_folder;
    private $imageName;
    private $fileArray;
    private $updateImage = 0;
    private $uptypes = array(
        'image/jpg',
        'image/jpeg',
        'image/png',
        'image/x-png');

    private $imageType = array('jpg', 'jpeg', 'png');
    const max_file_size = 2000000;


    /**
     * 构造函数
     * @param $file 上传的图片信息数组
     * @param $destination 上传文件路径
     * @param $name 上传的图片名（可自定义）没定义，上传后的图片名为time()
     * @param $dbPath 图片上传成功保存在数据库的路径
     * @param $update 判断是否更新原有图片 0表示不更新 ， 1 表示更新 。 更新会把已经存在的图片替换掉
     */
    public function __construct($file, $destination = "", $name = "", $dbPath = "", $update = 0)
    {

        if (strtolower($_SERVER['REQUEST_METHOD']) != 'post') {
            $this->imageStauts = 'Error! Wrong HTTP method!';
        }
        if (is_array($file) && count($file) > 0 && !empty($destination)) {
            $this->fileArray = $file;
            $this->destination_folder = $destination;
            $this->imageName = $name;
            $this->imagePathTemp = $dbPath;
            $this->updateImage = $update;

        } else {

            $this->imageStauts = '初始化失败';

        }

    }


    /**
     * 开始图片上传
     */
    public function imageStart()
    {
        if ($this->imageStauts === 1) {
            $this->imageCheck();

        }
        if ($this->imageStauts === 1) {
            $this->doWork();
        }
    }

    /**
     *
     * 图片的检查工作
     */
    private function imageCheck()
    {
        $file = $this->fileArray;
        //print_r($file);
        if (!is_uploaded_file($file['tmp_name']) && $this->imageStauts === 1) {
            $this->imageStauts = '图片不存在!';

        }

        if (uploadImage::max_file_size < $file['size'] && $this->imageStauts === 1) {
            $this->imageStauts = '文件太大';
        }

        //检查mime-type
//        if(!in_array(strtolower($file['type']), $this->uptypes) && $this->imageStauts === 1){
//            $this->imageStauts =  '不支持 '.$file['type'].' 类型的文件';
//        }
        //防止在图片元数据的Comment字段中加入了php代码
        //通过二进制匹配检查
        $fileInfo = pathinfo($this->fileArray['name']);
        $fileType = strtolower($fileInfo['extension']);
        if (!in_array($fileType, $this->imageType) && $this->imageStauts === 1) {
            $this->imageStauts = '不支持 ' . $fileType . ' 类型的文件';
        }

        if (!file_exists($this->destination_folder) && $this->imageStauts === 1) {
            mkdir($this->destination_folder, 0777);//设置文件权限
        }
    }

    /**
     *
     * 开始图片上传的工作
     */
    private function doWork()
    {
        $fileName = $this->fileArray['tmp_name'];
        $fileSize = getimagesize($fileName);
        $fileInfo = pathinfo($this->fileArray['name']);
        $fileType = strtolower($fileInfo['extension']);
        $n = !empty($this->imageName) ? $this->imageName : time();
        $destination = $this->destination_folder . $n . '.' . $fileType;//图片本地路径
        $this->imagePathTemp = $this->imagePathTemp . $n . '.' . $fileType;//将要保存在数据库的路径

        //上传图片，若图片存在不更新已有图片
        if (file_exists($destination) && $this->imageStauts === 1 && $this->updateImage == 0) {
            $this->imageStauts = '图片已存在';
        }

        //上传图片，若图片存在更新已有图片
        if ($this->imageStauts === 1 && $this->updateImage == 1) {
            $deleteIMageDestination = $this->destination_folder . $n; //图片保存本地路径，包含文件名，但不包含图片后缀名
            if ($this->deleteImage($deleteIMageDestination)) {

            } else {
                $this->imageStauts = '删除已存在图片失败';
            }
        }

        if (!move_uploaded_file($fileName, $destination) && $this->imageStauts === 1) {
            $this->imageStauts = '传输错误';
        }

        if ($this->imageStauts === 1) {
            $this->iamgePath = $this->imagePathTemp;
            return $this->imageStauts;
        }

    }

    /**
     * 删除图片
     * @param $path  图片在本地的保存路径
     * @return 成功返回1 失败返回0
     */
    private function deleteImage($path)
    {
        if (!empty($path)) {
            foreach ($this->imageType as $type) {
                $_path = $path . '.' . $type;
                if (file_exists($_path)) {
                    //echo $_path;
                    if (!unlink($_path)) {
                        $this->imageStauts = '删除已存在图片失败';
                        return 0;
                    }
                }
            }
            return 1;
        } else {
            $this->imageStauts = '待删除图片路径不能为空';
            return 0;
        }
    }


}

?>