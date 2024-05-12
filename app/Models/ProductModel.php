<?php
namespace App\Models;
use CodeIgniter\Model;

class ProductModel extends Model{
    protected $table = 'product';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = true;
    protected $useAutoIncrement = false;
    protected $useSoftDeletes = true;
    protected $beforeInsert = [];
    protected $allowedFields = [
        'id',
        'name',
        'slug',
        'price',
        'foto',
    ];
    public function __construct(){
        $connect = \Config\Database::connect();
        $this->db = $connect->table($this->table);
    }
    public function simpan($data){
        try {
            $this->db->set('id', 'UUID()', false);
			$this->db->set('created_at', 'NOW()', false);
			$this->db->set('updated_at', 'NOW()', false);
			$res = $this->db->insert($data);
			return $res;
        } catch (\Exception $e) {
           return $e->getMessage();
        }
    }

    public function ngupdate($id, $data){
        try {
			$this->db->set('updated_at', 'NOW()', false);
            $this->db->where('id', $id);
			$res = $this->db->update($data);
			return $res;
        } catch (\Exception $e) {
           return $e->getMessage();
        }
    }

    public function hapus($data) {
        try {
            if ($data['batch'] === true) {
                return $this->truncate();
            } else{
                return $this->delete($data['id']);
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getSingle($id){
        try {
            $this->db->where('id', $id);
            return $this->db->get()->getRow();
        } catch (\Exception $e) {
            return $e->getMessage();
         }
    }
}