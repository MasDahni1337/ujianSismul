<?php

namespace App\Controllers;

class ProductController extends BaseController
{
    public function index() {
        return view('Product/index');
    }

    public function list(){
        $param = $_REQUEST;        
        $data = $this->product->where('deleted_at IS NULL')->get()->getResult();
        $response = [
            "draw" => isset($param['draw']) ? $param['draw'] : 0,
            "recordsTotal" => count($data),
            "recordsFiltered" => count($data),
            "data" => $data,

        ];
        echo json_encode($response);
    }

    public function getProduct($id){
        $data = $this->product->getSingle($id);
        if($data){
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Product found.',
                'data' => $data
            ]);
        }else{
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Nothing product found',
                'data' => $data
            ]);
        }
       
    }

    public function save(){
        $validation = $this->validate([
            'name' => 'required|min_length[3]',
            'price' => 'required|decimal',
            'photo' => 'uploaded[photo]|mime_in[photo,image/jpg,image/jpeg,image/png,image/gif]'
        ]);
    
        if (!$validation) {
            return $this->response->setJSON([
                'error' => $this->validator->getErrors()
            ]);
        }
    
        $uuid = $this->generateUUID();
        $slug = url_title($this->request->getVar('name'), '-', true);
        $foto = $this->request->getFile('photo');
        $newname = '';
        if ($foto->isValid() && !$foto->hasMoved()) {
            $extension = $foto->getClientExtension();
            $dirUpload = "product/photo/";
            if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                $newname = $uuid . "-product-." . $extension;
                $foto->move($dirUpload, $newname);
            } else {
                return $this->response->setJSON([
                    'error' => 'Invalid file extension.'
                ]);
            }
        }
        $data = [
            "name" => $this->request->getVar('name'),
            'slug' => $slug,
            'price' => $this->request->getVar('price'),
            'foto' => $newname
        ];
        try {
            $this->product->simpan($data);
            return $this->response->setJSON([
                'status' => 200,
                'message' => 'Success save product',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Failed save product',
                'data' => $e->getMessage()
            ]);
        }
    }

    public function update(){
        $validation = $this->validate([
            'name' => 'required|min_length[3]',
            'price' => 'required|decimal',
        ]);
    
        if (!$validation) {
            return $this->response->setJSON([
                'error' => $this->validator->getErrors()
            ]);
        }
        $id = $this->request->getVar('productId');
        $uuid = $this->generateUUID();
        $slug = url_title($this->request->getVar('name'), '-', true);
        $foto = $this->request->getFile('photo');
        $newname = '';
        $data = [
            "name" => $this->request->getVar('name'),
            'slug' => $slug,
            'price' => $this->request->getVar('price'),
        ];
        if($foto){
            if ($foto->isValid() && !$foto->hasMoved()) {
                $extension = $foto->getClientExtension();
                $dirUpload = "product/photo/";
                if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                    $newname = $uuid . "-product-." . $extension;
                    $foto->move($dirUpload, $newname);
                    $data['foto'] = $newname;
                } else {
                    return $this->response->setJSON([
                        'error' => 'Invalid file extension.'
                    ]);
                }
            }
        }
        try {
            $this->product->ngupdate($id, $data);
            return $this->response->setJSON([
                'status' => 200,
                'message' => 'Success update product',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Failed update product',
                'data' => $e->getMessage()
            ]);
        }
    }

    public function singleDelete($id){
        try {
            $optDel = [
                "batch" => false,
                "id" => $id
            ];
            $data = $this->product->getSingle($id);
            $dir = './product/photo/';
            unlink($dir . $data->foto);
            $cek = $this->product->hapus($optDel);
            return $this->response->setJSON([
                'status' => 200,
                'message' => 'Success delete product',
                'data' => $id,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Failed delete product',
                'data' => $e->getMessage()
            ]);
        }
    }


    public function batchDelete(){
        try {
            $optDel = [
                "batch" => true,
            ];
            $dir = './product/photo/';
            $files = glob($dir . '*');
            foreach ($files as $file) {
                if(is_file($file)){
                    unlink($file);
                }
             }
            $res = $this->product->hapus($optDel);
            return $this->response->setJSON([
                'status' => 200,
                'message' => 'Success delete all product',
                'data' => $res
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 400,
                'message' => 'Failed delete all product',
                'data' => $e->getMessage()
            ]);
        }
    }
}
