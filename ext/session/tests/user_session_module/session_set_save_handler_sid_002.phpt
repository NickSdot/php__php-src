--TEST--
session_set_save_handler() with create_sid handler not returning string
--EXTENSIONS--
session
--FILE--
<?php

class MySession2 implements SessionHandlerInterface, SessionIdInterface {
    public $path;

    public function open($path, $name): bool {
        $this->path = $path . '/u_sess_' . $name;
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read($id): string|false {
        return file_get_contents($this->path . $id);
    }

    public function write($id, $data): bool {
        return file_put_contents($this->path . $id, $data);
    }

    public function destroy($id): bool {
        @unlink($this->path . $id);
        return false;
    }

    public function gc($maxlifetime): int|false {
        foreach (glob($this->path . '*') as $filename) {
            if (filemtime($filename) + $maxlifetime < time()) {
                @unlink($filename);
            }
        }
        return true;
    }

    #[ReturnTypeWillChange]
    public function create_sid() {
        return false;
    }

	public function validateId(string $id): bool {
	    return true;
	}
}

$save_path = TestPath::directory()->create();
session_save_path($save_path);
session_set_save_handler(new MySession2());

try {
    session_start();
} catch (Throwable $e) {
    echo $e::class, ': ', $e->getMessage(), "\n";
}

?>
--EXPECT--
TypeError: Session id must be of type string, bool given
