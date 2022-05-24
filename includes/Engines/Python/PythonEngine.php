<?php

namespace MediaWiki\Extension\FFI\Engines\Python;

use MediaWiki\Extension\FFI\Engines\Engine;
use PPFrame;
use Status;

/**
 * Engine implementation using Python.
 */
class PythonEngine extends Engine {
	/**
	 * @inheritDoc
	 */
	public function validateSource( string $script, Status &$status ): void {
		// TODO: Implement validateSource() method.
	}

	/**
	 * @inheritDoc
	 */
	public function executeScript( string $script, string $mainName, PPFrame $frame ): string {
		// TODO: Implement executeScript() method.
		return 'TODO';
	}

	/**
	 * @inheritDoc
	 */
	public function getHumanName(): string {
		return 'Python';
	}

	/**
	 * @inheritDoc
	 */
	public function getVersion(): ?string {
		// TODO: Implement getVersion() method.
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getLogo(): string {
		return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAMAAAC6V+0/AAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAACClBMVEUAAABAgL83erM4ebA2eK43d603dqs3dak2dKg3c6Q5erM4ebA3d643caI3ebA3ea0AgIA3dqs3cKA3d643d603d6s3cJ44dq42dqs2dKk2dKc2cqU2cqM2bpw2ebQ3ebA3eK43d603dqs3dqk3dKg3c6U3cqQ3caI2bZr/2Ev/2Ej/1kf/1EU3erE4ebA2bJn/2Uj/0kI3eLA2a5f/1kb/0T83eK42a5T/1UT/zz83caI4b6A4b542bpw2bps2bJk2a5c1apX80kb/00Q3dqs3cqM3cKH/31H/3k//3E7/3Ez/2Ur/10r/1kf/1UU4dak3caL/4FH/yzo3c6c4b6D/3k7/yTk1c6U3cJ7/3E7/yjn/xzg3caM3b543bp3/20z/00T/0kL/0UH/0D//zj//zTz/yzr/yjn/xzj/2kv/00P/0EL/0ED/zj7/zDz/2En/zj7/zTz/yzv/10f/zj7/zDP/yjz/yjr/1Ub/1ET/zDv/yTf/1UL/0UH/0ED/zj7/zTz/zDz/yzn/yjj/v0A3d603dqs3dak3dKc3c6U3cqQ3caI3cKA3b542bpw3eK42bZr/1kf/1UX/00Q2bJn/0kI3cqM2a5f/0UD/0UH/zz//zj3/1ET/zDz/3k//3E7/20z/2Ur/2En/1Ub/zTz/10f/zj7/yzr/2Uv/0kP/0D//00P/0ED///9ywrbbAAAAhXRSTlMABGu/6vn25bJUkOWrfs+DAu/bz/bV4G6IiIiI+OA9uMzMzMzMzMz84HDu5ncu+eB4VaHRe73fda/00K6qqqqqoldQ/PX+VFiou7u7u7vU4b9r8KiOwLA+i836N2Hugs3NzMzMzMzMskDNioiIiHjN0vDiyPsKb+Jm/rmdQqXY7vHjuWkEM28rJQAAAAFiS0dErSBiwh0AAAAHdElNRQfhCAoJATvpwCrIAAABH0lEQVQY02NgAAFGJmYWVjZ2Dk4GJMDFzdPa1t7R2cWLJMjHLyAIEusWQhIUFhEFi/WIIQmKS0hKSct09/TKQgXk5BUUlZRVVNXUe3r7NDS1tHUYGHT1+iF2ANX1TdA3mDhpsiGDEbLYFGOTSZOnmjKYgcWmQcSmm1tMnjrDkkEELGZlbWNrZ+/g6OQ8deas2QwuQDFXN3cPTy9vH1+/OSCxuQz+QHUBgfPmL1i4aOJisNiSIIZgoHkhoSCxpWCxZUuWhzGEu3b3REQii0VFMzDECMXGxa8AiiUkJiWnpKalZ0B9lQlSl5Wdk5uH5Pd8oNjKmasKCouQBIsXz1k9c1VJaVk5kmBF5eqZa5YtqSqvRg76mtq6+obGpuYWMA8ACBl/Ca2P4ogAAAAldEVYdGRhdGU6Y3JlYXRlADIwMTctMDgtMTBUMDk6MDE6NTkrMDA6MDAkiJ0oAAAAJXRFWHRkYXRlOm1vZGlmeQAyMDE3LTA4LTEwVDA5OjAxOjU5KzAwOjAwVdUllAAAAABJRU5ErkJggg==';
	}

	/**
	 * @inheritDoc
	 */
	public function getGeSHiName(): string {
		return 'python';
	}

	/**
	 * @inheritDoc
	 */
	public function getCodeEditorName(): string {
		return 'python';
	}
}
