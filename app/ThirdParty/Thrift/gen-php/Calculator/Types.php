<?php
namespace Calculator;

/**
 * Autogenerated by Thrift Compiler (0.10.0)
 *
 * DO NOT EDIT UNLESS YOU ARE SURE THAT YOU KNOW WHAT YOU ARE DOING
 *  @generated
 */
use Thrift\Base\TBase;
use Thrift\Type\TType;
use Thrift\Type\TMessageType;
use Thrift\Exception\TException;
use Thrift\Exception\TProtocolException;
use Thrift\Protocol\TProtocol;
use Thrift\Protocol\TBinaryProtocolAccelerated;
use Thrift\Exception\TApplicationException;


final class Operation {
  const ADD = 1;
  const SUBTRACT = 2;
  const MULTIPLY = 3;
  const DIVIDE = 4;
  static public $__names = array(
    1 => 'ADD',
    2 => 'SUBTRACT',
    3 => 'MULTIPLY',
    4 => 'DIVIDE',
  );
}

class Work {
  static $_TSPEC;

  /**
   * @var int
   */
  public $num1 = 0;
  /**
   * @var int
   */
  public $num2 = null;
  /**
   * @var int
   */
  public $op = null;
  /**
   * @var string
   */
  public $comment = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'num1',
          'type' => TType::I32,
          ),
        2 => array(
          'var' => 'num2',
          'type' => TType::I32,
          ),
        3 => array(
          'var' => 'op',
          'type' => TType::I32,
          ),
        4 => array(
          'var' => 'comment',
          'type' => TType::STRING,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['num1'])) {
        $this->num1 = $vals['num1'];
      }
      if (isset($vals['num2'])) {
        $this->num2 = $vals['num2'];
      }
      if (isset($vals['op'])) {
        $this->op = $vals['op'];
      }
      if (isset($vals['comment'])) {
        $this->comment = $vals['comment'];
      }
    }
  }

  public function getName() {
    return 'Work';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::I32) {
            $xfer += $input->readI32($this->num1);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::I32) {
            $xfer += $input->readI32($this->num2);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::I32) {
            $xfer += $input->readI32($this->op);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 4:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->comment);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('Work');
    if ($this->num1 !== null) {
      $xfer += $output->writeFieldBegin('num1', TType::I32, 1);
      $xfer += $output->writeI32($this->num1);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->num2 !== null) {
      $xfer += $output->writeFieldBegin('num2', TType::I32, 2);
      $xfer += $output->writeI32($this->num2);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->op !== null) {
      $xfer += $output->writeFieldBegin('op', TType::I32, 3);
      $xfer += $output->writeI32($this->op);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->comment !== null) {
      $xfer += $output->writeFieldBegin('comment', TType::STRING, 4);
      $xfer += $output->writeString($this->comment);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class InvalidOperation extends TException {
  static $_TSPEC;

  /**
   * @var int
   */
  public $whatOp = null;
  /**
   * @var string
   */
  public $why = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'whatOp',
          'type' => TType::I32,
          ),
        2 => array(
          'var' => 'why',
          'type' => TType::STRING,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['whatOp'])) {
        $this->whatOp = $vals['whatOp'];
      }
      if (isset($vals['why'])) {
        $this->why = $vals['why'];
      }
    }
  }

  public function getName() {
    return 'InvalidOperation';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::I32) {
            $xfer += $input->readI32($this->whatOp);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->why);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('InvalidOperation');
    if ($this->whatOp !== null) {
      $xfer += $output->writeFieldBegin('whatOp', TType::I32, 1);
      $xfer += $output->writeI32($this->whatOp);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->why !== null) {
      $xfer += $output->writeFieldBegin('why', TType::STRING, 2);
      $xfer += $output->writeString($this->why);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

final class Constant extends \Thrift\Type\TConstant {
  static protected $INT32CONSTANT;
  static protected $MAPCONSTANT;

  static protected function init_INT32CONSTANT() {
    return 9853;
  }

  static protected function init_MAPCONSTANT() {
    return array(
      "hello" => "world",
      "goodnight" => "moon",
    );
  }
}


