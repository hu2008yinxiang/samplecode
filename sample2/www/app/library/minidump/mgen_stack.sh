#!/bin/sh
curDir=$(dirname $(readlink -f $0))
tmp="tmp"
export PATH=$curDir:$PATH
so_files=$1
dump_files=$2
so_files=`ls $so_files/*.so`
dump_files=`ls $dump_files/*.dmp`
mkdir -p $tmp
for so_file in $so_files
do
	dump_syms_cmd="dump_syms $so_file 2>/dev/null > $tmp/tmp.sym"
	echo $so_file
	echo "$dump_syms_cmd"
	sh -c "$dump_syms_cmd"
	line=`head -1 $tmp/tmp.sym`
	index=1
	for seg in $line
	do
		
		case $index in
			1) so_type=$seg;;
			2) os_type=$seg;;
			3) arc_type=$seg;;
			4) sym_sign=$seg;;
			5) so_name=$seg;;
		esac 
		index=`expr $index + 1`
	done
	echo $line
	sym_dir=$tmp/symbol
	sym_file=$sym_dir/$so_name/$sym_sign/$so_name.sym
	mkdir -p `dirname $sym_file`
	mv $tmp/tmp.sym $sym_file
	echo $sym_file
done

for dump_file in $dump_files
do
	walk_stack_cmd="minidump_stackwalk $dump_file $sym_dir 2>/dev/null | grep '!' > $dump_file.stack"
	echo $walk_stack_cmd
	sh -c "$walk_stack_cmd"
	# cat $tmp/tmp.stack
done
#rm -rf $tmp
