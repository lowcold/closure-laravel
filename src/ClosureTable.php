<?php

namespace lowcold\ClosureTable;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait ClosureTable
{
    /**
     * 继承模型观察器
     */
    public static function boot()
    {
        // 继承
        parent::boot();

        // 监听创建方法
        static::created(function (Model $model) {
            $model->lockClosure();
        });

        // 监听删除
        static::deleted(function (Model $model) {
            DB::table(static::closure)->where('ancestor', $model->id)->delete();
        });
    }

    /**
     * 锁定关系
     */
    public function lockClosure()
    {
        $model = $this->toArray();
        // 判断上级是否为0
        if ($model[static::parent] != '0') {
            $table = env('DB_PREFIX') . static::closure;
            // 更新所有记录加1
            DB::insert("INSERT INTO {$table}(ancestor,descendant,distance) (SELECT ancestor,{$model["id"]},distance+1 FROM {$table} WHERE descendant={$model["parent"]})");
        }
        // 加入自身连接记录
        DB::table(static::closure)->insert([
                'ancestor' => $model['id'],
                'descendant' => $model['id'],
                'distance' => 0]
        );
    }

    /**
     * 重新绑定上级
     * $parent      新的上级(目标)
     */
    public function move($parent)
    {
        // 判断新上级是否与老上级不一样并且本身id不等于目标id
        if ($this->parent != $parent->id and $this->id != $parent->id) {
            // 原数据
            $thisData = $this->getDescendants()->get();
            // 目标数据
            $parentData = $parent->getDescendants()->get();
            // 删除所有原数据关系表
            DB::table(static::closure)->where('ancestor', $this->id)->where('distance', '!=', 0)->delete();
            // 删除所有目标数据关系表
            DB::table(static::closure)->where('ancestor', $parent->id)->where('distance', '!=', 0)->delete();
            // 更新上级id
            DB::table($this->getTable())->where('id', $this->id)->update([static::parent => $parent->id]);
            // 循环原数据并创建关系表
            $thisData->each(function ($item) {
                $item->lockClosure();
            });
            // 循环目标数据并创建关系表
            $parentData->each(function ($item) {
                $item->lockClosure();
            });
        }
    }

    /**
     * 获取所有下级用户，不包括自己
     */
    public function getDescendants()
    {
        return $this->queryClosure();
    }

    /**
     * 查询关联表数据
     * $type    1查询下级   2查询下级
     * $own     true包括自己    false不包括自己
     */
    public function queryClosure($type = 1, $own = false)
    {
        $query = null;
        // 组合关联表和祖先字段
        $ancestor = static::closure . '.ancestor';
        // 组合关联表和子代字段
        $descendant = static::closure . '.descendant';
        // 组合关联表和距离字段
        $distance = static::closure . '.distance';
        // 获得主表的键
        $keyName = $this->getQualifiedKeyName();
        // 判断是否为查询下级
        if ($type == 1) {
            // 组成查询下级join
            $query = $this->join(static::closure, $descendant, '=', $keyName)->where($ancestor, '=', $this->id);
        } else {
            // 组成查询上级join
            $query = $this->join(static::closure, $ancestor, '=', $keyName)->where($descendant, '=', $this->id);
        }
        // 判断是否包括自己
        $own = ($own === true ? '>=' : '>');
        $query->where($distance, $own, 0);
        return $query;
    }

    /**
     * 获取所有上级，包括自己
     */
    public function getAncestorAndOwn()
    {
        return $this->queryClosure(2, true);
    }

    /**
     * 获取所有上级，不包括自己
     */
    public function getAncestor()
    {
        return $this->queryClosure(2);
    }


    /**
     * 设置为顶级用户
     */
    public function setRoot()
    {
        // 所有原数据下级包括自己
        $thisData = $this->getDescendantsAndOwn()->get();
        // 删除所有原数据关系表
        DB::table(static::closure)->where('ancestor', $this->id)->delete();
        // 循环原数据并创建关系表
        $thisData->each(function ($item) {
            $item->lockClosure();
        });
    }

    /**
     * 获取所有下级用户，包括自己
     */
    public function getDescendantsAndOwn()
    {
        return $this->queryClosure(1, true);
    }
}
