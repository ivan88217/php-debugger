<?php

namespace Ivan88217;

class Debugger {
	public static $theme_printed = false;

	public static function theme() {
		if (Debugger::$theme_printed) {
			return;
		}

		Debugger::$theme_printed = true;

		$base_url = base_url();

		echo (
			"<script src=\"{$base_url}assets/js/main/shared/debug.js\"></script>".
			"<link href=\"{$base_url}assets/css/main/shared/debug.css\" rel=\"stylesheet\">".
			"<style>.debug-container{display:block}</style>"
		);
	}

	public static function log($value, $typed = true) {
		Debugger::theme();
		$html = Debugger::to_html($value, $typed);
		echo "<div class=\"debug-container\">$html</div>";
	}

	public static function to_html($value, $typed = true, $nesting_level = 0) {
		$type = gettype($value);
		$type_span_html = $typed ? Debugger::to_type_span_html($value) : "";
		$indent = str_repeat("&nbsp;", $nesting_level * 4);

		switch ($type) {
			case "NULL":
				return "<span class=\"reserved\">NULL</span>";

			case "double":
			case "integer":
				return "$type_span_html<span class=\"number\">$value</span>";

			case "boolean":
				$value_html = $value ? "true" : "false";
				return "$type_span_html<span class=\"reserved\">$value_html</span>";

			case "string":
				$value_html = str_replace("&", "&amp;", $value);
				return "$type_span_html<span class=\"string\">\"$value_html\"</span>";

			case "object":
				$value = get_object_vars($value);

			case "array":
				$item_indent = str_repeat("&nbsp;", ($nesting_level + 1) * 4);

				$items_html = implode(",\n", array_map(function ($key, $value) use ($typed, $nesting_level, $item_indent) {
					$html = Debugger::to_html($value, $typed, $nesting_level + 1);
					return "$item_indent<span class=\"string\">\"$key\"</span> => $html";
				}, array_keys($value), $value));

				if ($items_html == "") {
					return "$type_span_html<span>[]</span>";
				}

				$checkbox_attributes = $nesting_level ? "" : " checked";

				return (
					"<label>" .
					"<input type=\"checkbox\"$checkbox_attributes /><span class=\"arrow\"></span>" .
					"<label>" .
					"<input type=\"radio\" checked />{$type_span_html}[<span class=\"hide\"></span><span class=\"show\">\n$items_html\n$indent</span>]" .
					"</label>" .
					"</label>"
				);
		}
	}

	private static function to_type_span_html($value) {
		$type = gettype($value);
		$type_meta_span_html = "";

		if ($type === "array") {
			$type_meta = count($value);
			$type_meta_span_html = $type_meta ? " <span class=\"number\">$type_meta</span>" : "";
		}

		if ($type === "object") {
			$type_meta = get_class($value);
			$type_meta_span_html = " <span class=\"class-name\">$type_meta</span>";
		}

		return (
			"<span class=\"no-select\">" .
			"(<span class=\"reserved\">$type</span>$type_meta_span_html) " .
			"</span>"
		);
	}

	public static function form($url, $data = [], $method = "") {
		Debugger::theme();
		$form_items_html = Debugger::to_form_items_html($data);
	
		$auto_request_script_html = $method ? (
			"<script>document.body.onload = function () { document.querySelector('.debug-form-submit .$method').click(); }</script>"
		) : "";

		echo (
			"<div class=\"debug-container\">" .
			"<span>$form_items_html</span>\n" .
			"<div class=\"debug-form-submit\">" .
				"[<span class=\"reserved GET\">GET</span>][<span class=\"reserved POST\">POST</span>]<form action=\"$url\"></form>" .
			"</div>" .
			$auto_request_script_html .
			"</div>"
		);
	}

	public static function to_form_items_html($data = [], $prefix = "") {
		return implode("\n", array_map(function ($name, $value) use ($data, $prefix) {
			$name = $prefix ? "{$prefix}[$name]" : $name;
	
			if (gettype($value) == "array") {
				return Debugger::to_form_items_html($data, $name);
			}

			return "<span class=\"string\">$name</span>&#61;<span class=\"string\" contenteditable>$value</span>";
		}, array_keys($data), $data));
	}

	public static function table($value, $typed = false) {
		Debugger::theme();
		$html = Debugger::to_table_html($value, $typed);
		echo "<div class=\"debug-container\">$html</div>";
	}

	public static function to_table_html($value, $typed = false) {
		$keys_row_html = (
			"<tr>" .
			implode("", array_map(function ($key) {
				return "<th>$key</th>";
			}, array_keys($value[0]))) .
			"</tr>"
		);

		$item_rows_html = (
			implode("", array_map(function ($value) use ($typed) {
				return (
					"<tr><td><span class=\"hide-text\">[</span>" .
					implode("<span class=\"hide-text\">,</span></td><td>", array_map(function ($key, $value) use ($typed) {
						$html = Debugger::to_html($value, $typed);
						return "<span class=\"hide-text\">\"$key\" => </span>$html";
					}, array_keys($value), $value)) .
					"<span class=\"hide-text\">],</span></td></tr>"
				);
			}, $value))
		);

		return "<table><thead>$keys_row_html</thead><tbody>$item_rows_html</tbody></table>";
	}
}

function Debug_log($data, $typed = true, $indent = 0) {
	Debugger::log($data, $typed);
}

function Debug_log_string($data, $typed = true, $indent = 0) {
  return Debugger::to_html($data, $typed);
}

function Debug_theme() {
	Debugger::theme();
}

function Debug_form($url, $data = [], $method = "", $split = "\n", $prefix = "") {
  Debugger::form($url, $data, $method);
}

function Debug_table_string(array $data, $typed = true) {
  return Debugger::to_table_html($data, $typed);
}

function Debug_table($data, $typed = false) {
	Debugger::table($data, $typed);
}
