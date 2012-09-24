<?php


define('FLAG_MATCHED', 1 << 0);
class StringScanner implements ArrayAccess
{
	protected
		$str = '',
		$len = 0,
		$flags,
		$curr = 0,
		$prev = 0,
		$matches = array();


	public function __construct($str)
	{
		$this->set_string($str);
	}


	/*
	 * Reset the scan pointer (index 0) and clear matching data.
	 */
	public function reset()
	{
		$this->curr = 0;
		$this->flags &= ~FLAG_MATCHED;
		return $this;
	}

	/*
	 * Set the scan pointer to the end of the string and clear matching data.
	 */
	public function terminate()
	{
		$this->curr = $this->len;
		$this->flags &= ~FLAG_MATCHED;
		return $this;
	}

	/*
	 * Returns the string being scanned.
	 */
	protected function get_string()
	{
		return $this->str;
	}

	/*
	 * Changes the string being scanned to +str+ and resets the scanner.
	 * Returns +str+.
	 */
	protected function set_string($str)
	{
		$this->str = (string) $str;
		$this->len = strlen($this->str);
		$this->reset();
		return $this->str;
	}

	/*
	 * Appends +str+ to the string being scanned.
	 * This method does not affect scan pointer.
	 *
	 *   $s = new StringScanner("Fri Dec 12 1975 14:39");
	 *   $s->scan('/Fri /');        # -> "Fri "
	 *   $s->concat(" +1000 GMT");
	 *   $s->string;                # -> "Fri Dec 12 1975 14:39 +1000 GMT"
	 *   $s->scan('/Dec/');         # -> "Dec"
	 */
	public function concat($str)
	{
		$this->str = (string) $str;
		$this->len = strlen($this->str);
		return $this;
	}

	/*
	 * Returns the position of the scan pointer.  In the 'reset' position, this
	 * value is zero.  In the 'terminated' position (i.e. the string is exhausted),
	 * this value is the length of the string.
	 *
	 * In short, it's a 0-based index into the string.
	 *
	 *   $s = new StringScanner('test string');
	 *   $s->pos;                  # -> 0
	 *   $s->scan_until('/str/');  # -> "test str"
	 *   $s->pos;                  # -> 8
	 *   $s->terminate();          # -> #<StringScanner fin>
	 *   $s->pos;                  # -> 11
	 */
	protected function get_pos()
	{
		return $this->curr;
	}

	/*
	 * Modify the scan pointer.
	 *
	 *   $s = new StringScanner('test string');
	 *   $s->pos = 7;            # -> 7
	 *   $s->rest;               # -> "ring"
	 */
	protected function set_pos($value)
	{
		$value = (int) $value;
		if ($value < 0)
		{
			$value += $this->len;
		}
		if ($value < 0 || $value > $this->len)
		{
			throw new RangeException("index out of range");
		}
		return $this->curr = $value;
	}


	protected function do_scan($regex, $advance_pointer, $return_string, $headonly)
	{
		# Clear match status
		$this->flags &= ~FLAG_MATCHED;

		# Don't search pasted the end of the string
		if ($this->len - $this->curr < 0) {
			return null;
		}

		$ret = preg_match($headonly ? "{$regex}A" : $regex, $this->str, $this->matches, PREG_OFFSET_CAPTURE, $this->curr);

		# There was an error during the preg_match operation
		if ($ret === FALSE) {
			throw new Exception("Unknown PCRE error");
		}

		# Not matched
		if ($ret === 0) {
			return null;
		}

		# Set the matched flag
		$this->flags |= FLAG_MATCHED;

		# Set the length of the match
		$this->matches[0][2] = strlen($this->matches[0][0]);

		# Handle advancing the pointer
		$this->prev = $this->curr;
		if ($advance_pointer) {
			$this->curr = $this->matches[0][1] + $this->matches[0][2];
		}

		# Return the relevant match data
		return $return_string ? ($headonly ? $this->matched : $this->pre_match . $this->matched) : $this->matches[0][2];
	}


	/*
	 * Tries to match with +pattern+ at the current position. If there's a match,
	 * the scanner advances the "scan pointer" and returns the matched string.
	 * Otherwise, the scanner returns +nil+.
	 *
	 *   $s = new StringScanner('test string');
	 *   $s->scan(/\w+/);   # -> "test"
	 *   $s->scan(/\w+/);   # -> null
	 *   $s->scan(/\s+/);   # -> " "
	 *   $s->scan(/\w+/);   # -> "string"
	 *   $s->scan(/./);     # -> null
	 *
	 */
	public function scan($re)
	{
		return $this->do_scan($re, 1, 1, 1);
	}

	/*
	 * Tests whether the given +pattern+ is matched from the current scan pointer.
	 * Returns the length of the match, or +nil+.  The scan pointer is not advanced.
	 *
	 *   $s = new StringScanner('test string');
	 *   $s.match('/\w+/');   # -> 4
	 *   $s.match('/\w+/');   # -> 4
	 *   $s.match('/\s+/');   # -> nil
	 */
	public function match($re)
	{
		return $this->do_scan($re, 0, 0, 1);
	}

	/*
	 * Attempts to skip over the given +pattern+ beginning with the scan pointer.
	 * If it matches, the scan pointer is advanced to the end of the match, and the
	 * length of the match is returned.  Otherwise, +nil+ is returned.
	 *
	 * It's similar to #scan, but without returning the matched string.
	 *
	 *   $s = new StringScanner('test string');
	 *   $s->skip('/\w+/');   # -> 4
	 *   $s->skip('/\w+/');   # -> nil
	 *   $s->skip('/\s+/');   # -> 1
	 *   $s->skip('/\w+/');   # -> 6
	 *   $s->skip('/./');     # -> nil
	 *
	 */
	public function skip($re)
	{
		return $this->do_scan($re, 1, 0, 1);
	}

	/*
	 * This returns the value that #scan would return, without advancing the scan
	 * pointer.  The match register is affected, though.
	 *
	 *   $s = new StringScanner("Fri Dec 12 1975 14:39");
	 *   $s->check('/Fri/');            # -> "Fri"
	 *   $s->pos;                       # -> 0
	 *   $s->matched;                   # -> "Fri"
	 *   $s->check('/12/');             # -> nil
	 *   $s->matched  ;                 # -> nil
	 *
	 * Mnemonic: it "checks" to see whether a #scan will return a value.
	 */
	public function check($re)
	{
		return $this->do_scan($re, 0, 1, 1);
	}

	/*
	 * Tests whether the given +pattern+ is matched from the current scan pointer.
	 * Returns the matched string if +return_string_p+ is true.
	 * Advances the scan pointer if +advance_pointer_p+ is true.
	 * The match register is affected.
	 *
	 * "full" means "#scan with full parameters".
	 */
	public function scan_full($re, $return_string, $advance_pointer)
	{
		return $this->do_scan($re, $advance_pointer, $return_string, 1);
	}

	/*
	 * Scans the string _until_ the +pattern+ is matched.  Returns the substring up
	 * to and including the end of the match, advancing the scan pointer to that
	 * location. If there is no match, +nil+ is returned.
	 *
	 *   $s = StringScanner("Fri Dec 12 1975 14:39");
	 *   $s->scan_until('/1/')        # -> "Fri Dec 1"
	 *   $s->pre_match                # -> "Fri Dec "
	 *   $s->scan_until('/XYZ/')      # -> nil
	 */
	public function scan_until($re)
	{
		return $this->do_scan($re, 1, 1, 0);
	}

	/*
	 * Looks _ahead_ to see if the +pattern+ exists _anywhere_ in the string,
	 * without advancing the scan pointer.  This predicates whether a #scan_until
	 * will return a value.
	 *
	 *   $s = new StringScanner('test string');
	 *   $s->exist('/s/');         # -> 3
	 *   $s->scan('/test/');       # -> "test"
	 *   $s->exist('/s/');         # -> 6
	 *   $s->exist('/e/');         # -> nil
	 */
	public function exist($re)
	{
		return $this->do_scan($re, 0, 0, 0);
	}

	/*
	 * Advances the scan pointer until +pattern+ is matched and consumed.  Returns
	 * the number of bytes advanced, or +nil+ if no match was found.
	 *
	 * Look ahead to match +pattern+, and advance the scan pointer to the _end_
	 * of the match.  Return the number of characters advanced, or +nil+ if the
	 * match was unsuccessful.
	 *
	 * It's similar to #scan_until, but without returning the intervening string.
	 *
	 *   $s = new StringScanner("Fri Dec 12 1975 14:39");
	 *   $s->skip_until('/12/');      # -> 10
	 *   $s                           #
	 */
	public function skip_until($re)
	{
		return $this->do_scan($re, 1, 0, 0);
	}

	/*
	 * This returns the value that #scan_until would return, without advancing the
	 * scan pointer.  The match register is affected, though.
	 *
	 *   $s = new StringScanner("Fri Dec 12 1975 14:39");
	 *   $s->check_until('/12/');       # -> "Fri Dec 12"
	 *   $s->pos;                       # -> 0
	 *   $s->matched;                   # -> 12
	 *
	 * Mnemonic: it "checks" to see whether a #scan_until will return a value.
	 */
	public function check_until($re)
	{
		return $this->do_scan($re, 0, 1, 0);
	}

	/*
	 * Scans the string _until_ the +pattern+ is matched.
	 * Returns the matched string if +return_string_p+ is true, otherwise
	 * returns the number of bytes advanced.
	 * Advances the scan pointer if +advance_pointer_p+, otherwise not.
	 * This method does affect the match register.
	 */
	public function search_full($re, $return_string, $advance_pointer)
	{
		return $this->do_scan($re, $advance_pointer, $return_string, 0);
	}

	/*
	 * Scans one character and returns it.
	 * This method is multi-byte character sensitive.
	 * See also #get_byte.
	 *
	 *   $s = new StringScanner('ab');
	 *   $s->getch();      # => "a"
	 *   $s->getch();      # => "b"
	 *   $s->getch();      # => nil
	 *
	 *   $s = new StringScanner("\244\242");
	 *   $s->getch();      # => "\244\242"   # Japanese hira-kana "A" in EUC-JP
	 *   $s->getch();      # => nil
	 */
	public function getch()
	{
		$this->flags &= ~FLAG_MATCHED;
		if ($this->eos)
		{
			return null;
		}

		$len = 1;
		if ($this->curr + $len > $this->len)
		{
			$len = $this->len - $this->curr;
		}
		$this->prev = $this->curr;
		$this->curr += $len;

		$this->matches[0] = array(
			0 => mb_substr($this->str, $this->prev, $len),
			1 => $this->prev,
			2 => $len);

		$this->flags |= FLAG_MATCHED;
		return $this->matched;
	}

	/*
	 * Scans one byte and returns it.
	 * This method is NOT multi-byte character sensitive.
	 * See also #getch.
	 *
	 *   $s = new StringScanner('ab');
	 *   $s->get_byte();     # => "a"
	 *   $s->get_byte();     # => "b"
	 *   $s->get_byte();     # => nil
	 *
	 *   $s = new StringScanner("\244\242");
	 *   $s->get_byte();     # => "\244"
	 *   $s->get_byte();     # => "\242"
	 *   $s->get_byte();     # => nil
	 */
	public function get_byte()
	{
		$this->flags &= ~FLAG_MATCHED;
		if ($this->eos)
		{
			return null;
		}

		$len = 1;
		if ($this->curr + $len > $this->len)
		{
			$len = $this->len - $this->curr;
		}
		$this->prev = $this->curr;
		$this->curr += $len;

		$this->matches[0] = array(
			0 => substr($this->str, $this->prev, $len),
			1 => $this->prev,
			2 => $len);

		$this->flags |= FLAG_MATCHED;
		return $this->matched;
	}

	/*
	 * Extracts a string corresponding to <tt>string[pos,len]</tt>, without
	 * advancing the scan pointer.
	 *
	 *   s = StringScanner.new('test string')
	 *   s.peek(7)          # => "test st"
	 *   s.peek(7)          # => "test st"
	 *
	 */
	public function peek($len)
	{
		if ($this->curr > $this->len)
		{
			return null;
		}
		if ($this->curr + $len > $this->len)
		{
			$len = $this->len - $this->curr;
		}
		return substr($this->str, $this->curr, $len);
	}

	/*
	 * Set the scan pointer to the previous position.  Only one previous position is
	 * remembered, and it changes with each scanning operation.
	 *
	 *   $s = new StringScanner('test string');
	 *   $s->scan('/\w+/');     # => "test"
	 *   $s->unscan();
	 *   $s->scan('/../');      # => "te"
	 *   $s->scan('/\d/');      # => nil
	 *   $s->unscan();          # ScanError: unscan failed: previous match had failed
	 */
	public function unscan()
	{
		if (!$this->matched_p)
		{
			throw new Exception("unscan failed: previous match had failed");
		}
		$this->curr = $this->prev;
		$this->flags &= ~FLAG_MATCHED;
		return $this;
	}

	/*
	 * Returns +true+ iff the scan pointer is at the beginning of the line.
	 *
	 *   $s = new StringScanner("test\ntest\n");
	 *   $s->bol;             # => true
	 *   $s->scan('/te/');
	 *   $s->bol;             # => false
	 *   $s->scan('/st\n/');
	 *   $s->bol;             # => true
	 *   $s->terminate();
	 *   $s->bol;             # => true
	 */
	protected function get_bol()
	{
		return ($this->curr === 0 || $this->str[$this->curr - 1] === "\n");
	}

	/*
	 * Returns +true+ if the scan pointer is at the end of the string.
	 *
	 *   $s = new StringScanner('test string');
	 *   $s->eos;            # => false
	 *   $s->scan('/test/');
	 *   $s->eos;            # => false
	 *   $s->terminate();
	 *   $s->eos             # => true
	 */
	protected function get_eos()
	{
		return ($this->curr >= $this->len);
	}

	/*
	 * Returns +true+ iff the last match was successful.
	 *
	 *   $s = new StringScanner('test string');
	 *   $s->match('/\w+/');     # => 4
	 *   $s->matched_p;          # => true
	 *   $s->match('/\d+/');     # => nil
	 *   $s->matched_p;          # => false
	 */
	protected function get_matched_p()
	{
		return (bool) ($this->flags & FLAG_MATCHED);
	}

	/*
	 * Returns the last matched string.
	 *
	 *   $s = new StringScanner('test string');
	 *   $s->match('/\w+/');     # -> 4
	 *   $s->matched;            # -> "test"
	 */
	protected function get_matched()
	{
		if (!$this->matched_p)
		{
			return null;
		}
		return substr($this->str, $this->matches[0][1], $this->matches[0][2]);
	}

	/*
	 * Returns the size of the most recent match (see #matched), or +nil+ if there
	 * was no recent match.
	 *
	 *   $s = new StringScanner('test string');
	 *   $s->check('/\w+/');        # -> "test"
	 *   $s->matched_size;          # -> 4
	 *   $s->check('/\d+/');        # -> nil
	 *   $s->matched_size;          # -> nil
	 */
	protected function get_matched_size()
	{
		return $this->matched_p ? $this->matches[0][2] : null;
	}

	/*
	 * Return the n-th subgroup in the most recent match.
	 *
	 *   $s = new StringScanner.new("Fri Dec 12 1975 14:39");
	 *   $s.scan('/(\w+) (\w+) (\d+) /');     # -> "Fri Dec 12 "
	 *   $s[0];                               # -> "Fri Dec 12 "
	 *   $s[1];                               # -> "Fri"
	 *   $s[2];                               # -> "Dec"
	 *   $s[3];                               # -> "12"
	 *   $s.post_match;                       # -> "1975 14:39"
	 *   $s.pre_match;                        # -> ""
	 */
	public function offsetGet($index) {
		return $this->matched_p ? $this->matches[$index][0] : null;
	}
	public function offsetUnset($index) {}
	public function offsetExists($index) {}
	public function offsetSet($index, $value) {}

	/*
	 * Return the pre-match (in the regular expression sense) of the last scan.
	 *
	 *   $s = new StringScanner('test string');
	 *   $s->scan('/\w+/');        # -> "test"
	 *   $s->scan('/\s+/');        # -> " "
	 *   $s->pre_match;            # -> "test"
	 *   $s->post_match;           # -> "string"
	 */
	protected function get_pre_match()
	{
		if (!$this->matched_p)
		{
			return null;
		}
		return substr($this->str, 0, $this->matches[0][1]);
	}

	/*
	 * Return the post-match (in the regular expression sense) of the last scan.
	 *
	 *   $s = new StringScanner('test string');
	 *   $s->scan('/\w+/');        # -> "test"
	 *   $s->scan('/\s+/');        # -> " "
	 *   $s->pre_match;            # -> "test"
	 *   $s->post_match;           # -> "string"
	 */
	protected function get_post_match()
	{
		if (!$this->matched_p)
		{
			return null;
		}
		return substr($this->str, $this->matches[0][2], $this->len);
	}

	/*
	 * Returns the "rest" of the string (i.e. everything after the scan pointer).
	 * If there is no more data (eos = true), it returns "".
	 */
	protected function get_rest()
	{
		if ($this->eos)
		{
			return '';
		}
		return substr($this->str, $this->curr, $this->len);
	}

	/*
	 * Equivalent to strlen(s->rest).
	 */
	protected function rest_size()
	{
		return $this->eos ? 0 : $this->len - $this->curr;
	}

	/*
	 * Returns a string that represents the StringScanner object, showing:
	 * - the current position
	 * - the size of the string
	 * - the characters surrounding the scan pointer
	 *
	 *   $s = new StringScanner.new("Fri Dec 12 1975 14:39");
	 *   $s->inspect();            # -> '#<StringScanner 0/21 @ "Fri D...">'
	 *   $s->scan_until('/12/');   # -> "Fri Dec 12"
	 *   $s->inspect();            # -> '#<StringScanner 10/21 "...ec 12" @ " 1975...">'
	 */
	public function inspect()
	{
		if (empty($this->str)) {
			return sprintf("#<%s (uninitialized)>", __CLASS__);
		}
		if ($this->eos) {
			return sprintf("#<%s fin>", __CLASS__);
		}
		if ($this->bol) {
			$a = substr($this->str, 0, 8);
			if (strlen($a) > 5)
			{
				$a = substr($a, 0, 5) . "...";
			}
			return sprintf("#<%s %d/%d @ %s>", __CLASS__, $this->curr, $this->len, "\"$a\"");
		}
		$a = substr(substr($this->str, 0, $this->curr), -7);
		$b = substr($this->str, $this->curr, 8);
		if (strlen($a) > 5)
		{
			$a = "..." . substr($a, -5);
		}
		if (strlen($b) > 5)
		{
			$b = substr($b, 0, 5) . "...";
		}
		return sprintf("#<%s %d/%d %s @ %s>", __CLASS__, $this->curr, $this->len, "\"$a\"", "\"$b\"");
	}


	public function __toString()
	{
		return $this->inspect();
	}

	public function __get($name)
	{
		if (method_exists($this, "get_$name"))
		{
			$name = "get_$name";
			return $this->$name();
		}
	}

	public function __set($name, $value)
	{
		if (method_exists($this,"set_$name"))
		{
			$name = "set_$name";
			return $this->$name($value);
		}
	}

}