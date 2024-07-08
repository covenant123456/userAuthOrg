<?php
// App\Models\Organisation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    protected $primaryKey = 'orgId';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'orgId', 'name', 'description', 'userId'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
